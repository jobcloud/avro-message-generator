<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;

/**
 * Class SchemaFieldValueResolver
 */
class SchemaFieldValueResolver implements SchemaFieldValueResolverInterface
{
    private Faker $faker;

    private DataDefinitionInterface $dataDefinition;

    private DataDefinitionInterface $globalDataDefinition;

    /** @var mixed */
    private $predefinedPayload;

    /**
     * @param Faker $faker
     * @param DataDefinitionInterface $dataDefinition
     * @param DataDefinitionInterface $globalDataDefinition
     * @param mixed $predefinedPayload
     */
    public function __construct(
        Faker $faker,
        DataDefinitionInterface $dataDefinition,
        DataDefinitionInterface $globalDataDefinition,
        $predefinedPayload
    ) {
        $this->faker = $faker;
        $this->dataDefinition = $dataDefinition;
        $this->globalDataDefinition = $globalDataDefinition;
        $this->predefinedPayload = $predefinedPayload;
    }


    /**
     * @param string $schemaType
     * @param string|null $fieldName
     * @param array<integer, string> $path
     * @return mixed
     * @throws MissingCommandExecutorException
     */
    public function getValue(string $schemaType, ?string $fieldName, array $path)
    {
        $fieldName = $fieldName ?? 0;

        // root schema
        if ([] === $path) {
            if (null !== $this->predefinedPayload) {
                return $this->predefinedPayload;
            }

            if ($this->dataDefinition->hasDataDefinitionField($fieldName)) {
                $field = $this->dataDefinition->getDataDefinitionField($fieldName);

                return $field->getValue($this->faker);
            }

            return $this->generateValueBySchemaType($schemaType);
        }

        // bested schema
        $predefinedFields = $this->getPredefinedFieldsFromPath($path);

        if (array_key_exists($fieldName, $predefinedFields)) {
            return $predefinedFields[$fieldName];
        }


    }

    /**
     * @param string $schemaType
     * @return mixed
     */
    private function generateValueBySchemaType(string $schemaType)
    {

    }

    /**
     * @param array<integer, string> $path
     * @return array<string, mixed>
     */
    private function getPredefinedFieldsFromPath(array $path): array
    {
        $fields = [];



        return $fields;
    }
}
