<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Integration;

use Faker\Factory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidator;
use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProvider;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJsonException;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactory;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactory;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Payload\PayloadGenerator
 */
class PayloadGeneratorTest extends TestCase
{
    /** @var string */
    private const DATA_DEFINITION_ROOT_DIR_PATH = './tests/Integration/Definitions';

    /** @var string */
    private const SCHEMAS_ROOT_DIR_PATH = './tests/Integration/Schemas';

    public function testVacancyMessage(): void
    {
        $topicName = 'marketplace.core.entity.vacancy';

        $schemaDefinition = file_get_contents(self::SCHEMAS_ROOT_DIR_PATH . '/' . $topicName . '-value.avsc');

        $predefinedPayload = [
            'id' => 'idFromPayload',
            'metadataApiEmploymentTypeId' => 100,
            'metadataApiEducationLevelId' => 111,
            'salaryFrom' => null,
            'address' => [
                'address' => [
                    'street' => 'streetFromPredefinedPayload',
                    'addition' => null,
                    'postalCode' => 'postalCodeFromPredefinedPayload',
                    'city' => 'cityFromPredefinedPayload',
                    'countryIsoCode' => null,
                    'latitude' => 111.11,
                    'longitude' => 222.22
                ]
            ],
            'updatedAt' => null
        ];

        $payload = $this->getPayload($topicName, $schemaDefinition, $predefinedPayload);

        self::assertSame('idFromPayload', $payload['id']);
        self::assertSame('accountIdFromDataDefinitionJson', $payload['accountId']);
        self::assertSame('locationIdFromGlobalJson', $payload['locationId']);
        self::assertIsString($payload['jobPosition']);
        self::assertIsInt($payload['employmentGradeFrom']);
        self::assertIsInt($payload['employmentGradeTo']);
        self::assertSame(100, $payload['metadataApiEmploymentTypeId']);
        self::assertIsInt($payload['metadataApiEmploymentPositionId']);
        self::assertSame('metadataApiLanguageIdFromDataDefinitionJson', $payload['languageRequirements'][0]['metadataApiLanguageId']);
        self::assertSame(1234, $payload['languageRequirements'][0]['metadataApiLanguageLevelId']);
        self::assertSame('0-1', $payload['experience']);
        self::assertSame(111, $payload['metadataApiEducationLevelId']);
        self::assertNull($payload['salaryFrom']);
        self::assertNull($payload['salaryTo']);
        self::assertIsBool($payload['showSalary']);
        self::assertSame('descriptionFromGlobalJson', $payload['description']);
        self::assertIsString($payload['skillRequirements'][0]['text']);
        self::assertSame('benefitsFromDataDefinitionJson', $payload['benefits'][0]);
        self::assertSame('streetFromPredefinedPayload', $payload['address']['street']);
        self::assertNull($payload['address']['addition']);
        self::assertSame('postalCodeFromPredefinedPayload', $payload['address']['postalCode']);
        self::assertSame('cityFromPredefinedPayload', $payload['address']['city']);
        self::assertNull($payload['address']['countryIsoCode']);
        self::assertSame(111.11, $payload['address']['latitude']);
        self::assertSame(222.22, $payload['address']['longitude']);
        self::assertSame('createdAtFromGlobalJson', $payload['createdAt']);
        self::assertNull($payload['updatedAt']);
        self::assertIsString($payload['_version']);
    }

    public function testLocationMessage(): void
    {
        $topicName = 'marketplace.core.entity.location';

        $schemaDefinition = file_get_contents(self::SCHEMAS_ROOT_DIR_PATH . '/' . $topicName . '-value.avsc');

        $predefinedPayload = [
            'address' => [
                'address' => [
                    'street' => 'streetFromPredefinedPayload',
                    'addition' => null,
                    'postalCode' => 'postalCodeFromPredefinedPayload',
                    'city' => 'cityFromPredefinedPayload',
                    'countryIsoCode' => null,
                    'latitude' => 111.11,
                    'longitude' => 222.22
                ]
            ],
            'updatedBy' => 'updatedByPredefinedPayload'
        ];

        $payload = $this->getPayload($topicName, $schemaDefinition, $predefinedPayload);

        self::assertSame('idFromGlobalJson', $payload['id']);
        self::assertSame('companyProfileIdFromDataDefinitionJson', $payload['companyProfileId']);
        self::assertSame('streetFromPredefinedPayload', $payload['address']['street']);
        self::assertNull($payload['address']['addition']);
        self::assertSame('postalCodeFromPredefinedPayload', $payload['address']['postalCode']);
        self::assertSame('cityFromPredefinedPayload', $payload['address']['city']);
        self::assertNull($payload['address']['countryIsoCode']);
        self::assertSame(111.11, $payload['address']['latitude']);
        self::assertSame(222.22, $payload['address']['longitude']);
        self::assertSame('createdByFromGlobalJson', $payload['createdBy']);
        self::assertSame('updatedByPredefinedPayload', $payload['updatedBy']);
        self::assertSame('createdAtFromGlobalJson', $payload['createdAt']);
        self::assertIsString($payload['updatedAt']);
        self::assertIsString($payload['_version']);
    }

    /**
     * @param string $topicName
     * @param string $schemaDefinition
     * @param array $predefinedPayload
     * @return array
     * @throws IncorrectDataDefinitionJsonException
     * @throws InvalidDataDefinitionFieldException
     * @throws UnexistingDataDefinitionException
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function getPayload(string $topicName, string $schemaDefinition, array $predefinedPayload): array
    {
        $schemaFieldValueResolverFactory = $this->getSchemaFieldValueResolverFactory();

        $payloadGeneratorFactory = new PayloadGeneratorFactory();

        $decodedSchema = json_decode((string) $schemaDefinition, true, 512, JSON_THROW_ON_ERROR);

        $schemaFieldValueResolver = $schemaFieldValueResolverFactory->create($topicName, $predefinedPayload);

        $payloadGenerator = $payloadGeneratorFactory->create($schemaFieldValueResolver);

        return $payloadGenerator->generate($decodedSchema);
    }

    /**
     * @return SchemaFieldValueResolverFactoryInterface
     * @throws IncorrectDataDefinitionJsonException
     * @throws InvalidDataDefinitionFieldException
     */
    private function getSchemaFieldValueResolverFactory(): SchemaFieldValueResolverFactoryInterface
    {
        $faker = Factory::create();

        $dataDefinitionFieldValidator = new DataDefinitionFieldValidator();

        $dataDefinitionFieldFactory = new DataDefinitionFieldFactory();

        $dataDefinitionFactory = new DataDefinitionFactory($dataDefinitionFieldValidator, $dataDefinitionFieldFactory);

        $dataDefinitionProvider = new DataDefinitionProvider(self::DATA_DEFINITION_ROOT_DIR_PATH, $dataDefinitionFactory);

        $dataDefinitionProvider->load();

        return new SchemaFieldValueResolverFactory($faker, $dataDefinitionProvider);
    }
}