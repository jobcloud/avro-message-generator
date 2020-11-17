<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField as Field;
use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

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

        $fieldKey = implode(Field::PATH_DELIMITER, $path) . Field::PATH_DELIMITER . $fieldName;

        if ($this->dataDefinition->hasDataDefinitionField($fieldKey)) {
            $field = $this->dataDefinition->getDataDefinitionField($fieldKey);

            return $field->getValue($this->faker);
        }

        if ($this->globalDataDefinition->hasDataDefinitionField($fieldKey)) {
            $field = $this->globalDataDefinition->getDataDefinitionField($fieldKey);

            return $field->getValue($this->faker);
        }

        return $this->generateValueBySchemaType($schemaType);
    }

    /**
     * @param string $schemaType
     * @return mixed
     */
    private function generateValueBySchemaType(string $schemaType)
    {
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
                return $this->faker->regexify('[A-Za-z_][A-Za-z0-9_]*');
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
