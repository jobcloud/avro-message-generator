<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField as Field;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

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
     * @param bool $isRootSchema
     * @return mixed
     */
    public function getValue(array $decodedSchema, array $path, bool $isRootSchema = false)
    {
        $fieldName = $decodedSchema['name'] ?? 0;

        if ($isRootSchema) {
            if (null !== $this->predefinedPayload) {
                return $this->predefinedPayload;
            }

            if (null !== $this->dataDefinition && $this->dataDefinition->hasDataDefinitionField($fieldName)) {
                /** @var DataDefinitionFieldInterface $field */
                $field = $this->dataDefinition->getDataDefinitionField($fieldName);

                return $this->resolveValue($field);
            }
        } else {
            // nested schema
            $predefinedFields = $this->getPredefinedFieldsFromPath($path);

            if (array_key_exists($fieldName, $predefinedFields)) {
                return $predefinedFields[$fieldName];
            }

            $fieldKey = trim(implode(Field::PATH_DELIMITER, $path) . Field::PATH_DELIMITER . $fieldName, '.');

            if (null !== $this->dataDefinition && $this->dataDefinition->hasDataDefinitionField($fieldKey)) {
                /** @var DataDefinitionFieldInterface $field */
                $field = $this->dataDefinition->getDataDefinitionField($fieldKey);

                return $this->resolveValue($field);
            }
        }

        if (null !== $this->globalDataDefinition && $this->globalDataDefinition->hasDataDefinitionField($fieldName)) {
            /** @var DataDefinitionFieldInterface $field */
            $field = $this->globalDataDefinition->getDataDefinitionField($fieldName);

            return $this->resolveValue($field);
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
                return $this->faker->title() === 'Mr.';
            case AvroSchemaTypes::INT_TYPE:
            case AvroSchemaTypes::LONG_TYPE:
                return $this->faker->randomDigit;
            case AvroSchemaTypes::FLOAT_TYPE:
            case AvroSchemaTypes::DOUBLE_TYPE:
                return $this->faker->randomFloat(1);
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

        if (is_array($this->predefinedPayload)) {
            if ($path === []) {
                $fields = $this->predefinedPayload;
            } else {
                $pathCount = count($path);

                $predefinedPayload = $this->predefinedPayload;

                for ($i = 0; $i < $pathCount; $i++) {
                    if (!is_array($predefinedPayload[$path[$i]])) {
                        break;
                    }

                    if ($i === $pathCount - 1) {
                        $fields = $predefinedPayload[$path[$i]];

                        break;
                    }

                    $predefinedPayload = $predefinedPayload[$path[$i]];
                }
            }
        }

        return $fields;
    }

    /**
     * @param DataDefinitionFieldInterface $field
     * @return mixed
     */
    private function resolveValue(DataDefinitionFieldInterface $field)
    {
        if ($field->isCommandField()) {
            /** @phpstan-ignore-next-line */
            return call_user_func_array(array($this->faker, $field->getCommand()), $field->getArguments());
        }

        return $field->getValue();
    }
}
