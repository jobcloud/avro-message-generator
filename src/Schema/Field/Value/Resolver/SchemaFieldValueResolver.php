<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField as Field;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

/**
 * Class SchemaFieldValueResolver
 */
class SchemaFieldValueResolver implements SchemaFieldValueResolverInterface
{
    private Faker $faker;

    private ?DataDefinitionInterface $dataDefinition;

    private ?DataDefinitionInterface $globalDataDefinition;

    /** @var mixed */
    private $predefinedPayload;

    /**
     * @param Faker $faker
     * @param DataDefinitionInterface|null $dataDefinition
     * @param DataDefinitionInterface|null $globalDataDefinition
     * @param mixed $predefinedPayload
     */
    public function __construct(
        Faker $faker,
        ?DataDefinitionInterface $dataDefinition,
        ?DataDefinitionInterface $globalDataDefinition,
        $predefinedPayload
    ) {
        $this->faker = $faker;
        $this->dataDefinition = $dataDefinition;
        $this->globalDataDefinition = $globalDataDefinition;
        $this->predefinedPayload = $predefinedPayload;
    }


    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<integer, string> $path
     * @return mixed
     * @throws MissingCommandExecutorException
     */
    public function getValue(array $decodedSchema, array $path)
    {
        $schemaType = $decodedSchema['type'];

        $fieldName = $decodedSchema['name'] ?? 0;

        // root schema
        if ([] === $path) {
            if (null !== $this->predefinedPayload) {
                return $this->predefinedPayload;
            }

            if (null !== $this->dataDefinition && $this->dataDefinition->hasDataDefinitionField($fieldName)) {
                /** @var DataDefinitionFieldInterface $field */
                $field = $this->dataDefinition->getDataDefinitionField($fieldName);

                return $field->getValue($this->faker);
            }

            if (
                null !== $this->globalDataDefinition &&
                $this->globalDataDefinition->hasDataDefinitionField($fieldName)
            ) {
                /** @var DataDefinitionFieldInterface $field */
                $field = $this->globalDataDefinition->getDataDefinitionField($fieldName);

                return $field->getValue($this->faker);
            }

            return $this->generateValueBySchemaType($decodedSchema);
        }

        // nested schema
        $predefinedFields = $this->getPredefinedFieldsFromPath($path);

        if (array_key_exists($fieldName, $predefinedFields)) {
            return $predefinedFields[$fieldName];
        }

        $fieldKey = trim(implode(Field::PATH_DELIMITER, $path) . Field::PATH_DELIMITER . $fieldName, '.');

        if (null !== $this->dataDefinition && $this->dataDefinition->hasDataDefinitionField($fieldKey)) {
            /** @var DataDefinitionFieldInterface $field */
            $field = $this->dataDefinition->getDataDefinitionField($fieldKey);

            return $field->getValue($this->faker);
        }

        if (null !== $this->globalDataDefinition && $this->globalDataDefinition->hasDataDefinitionField($fieldName)) {
            /** @var DataDefinitionFieldInterface $field */
            $field = $this->globalDataDefinition->getDataDefinitionField($fieldName);

            return $field->getValue($this->faker);
        }

        return $this->generateValueBySchemaType($decodedSchema);
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @return mixed
     */
    private function generateValueBySchemaType(array $decodedSchema)
    {
        $schemaType = $decodedSchema['type'];

        switch ($schemaType) {
            case AvroSchemaTypes::NULL_TYPE:
                return null;
            case AvroSchemaTypes::BOOLEAN_TYPE:
                return rand(0, 1) === 1;
            case AvroSchemaTypes::INT_TYPE:
            case AvroSchemaTypes::LONG_TYPE:
                return $this->faker->randomDigit;
            case AvroSchemaTypes::FLOAT_TYPE:
            case AvroSchemaTypes::DOUBLE_TYPE:
                return $this->faker->randomFloat(2);
            case AvroSchemaTypes::ENUM_TYPE:
                $symbols = $decodedSchema['symbols'];

                shuffle($symbols);


                return $symbols[0];
            default: // AvroSchemaTypes::STRING_TYPE
                return $this->faker->word;
        }
    }

    /**
     * @param array<integer, string> $path
     * @return array<string, mixed>
     */
    private function getPredefinedFieldsFromPath(array $path): array
    {
        $fields = [];

        $pathCount = count($path);

        for ($i = 0; $i < $pathCount; $i++) {
            if (!is_array($this->predefinedPayload[$path[$i]])) {
                break;
            }

            if ($i === $pathCount - 1) {
                $fields = $this->predefinedPayload[$path[$i]];
            }
        }

        return $fields;
    }
}
