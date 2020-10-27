<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Faker\Generator as Faker;
use InvalidArgumentException;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionStructure;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private Faker $faker;

    /**
     * @param Faker $faker
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|InvalidDataDefinitionStructure
     */
    public function generate(array $decodedSchema, array $dataDefinition, $predefinedPayload = null)
    {
        if (!isset($decodedSchema['type'])) {
            throw new UnsupportedAvroSchemaTypeException('Schema must contain type attribute.');
        }

        if ($this->isSimpleSchemaMapping($decodedSchema['type'])) {
            if (null !== $predefinedPayload) {
                return $predefinedPayload;
            }

            return $this->applyData($dataDefinition);
        }

        return $this->applyDataToComplexTypeSchema($decodedSchema, $dataDefinition, $predefinedPayload);
    }

    /**
     * @param array<string, mixed> $dataDefinition
     * @return mixed
     * @throws InvalidDataDefinitionStructure
     */
    private function applyData(array $dataDefinition)
    {
        $this->validateSimpleSchemaTypeDataDefinition($dataDefinition);

        if ($dataDefinition['type'] === 'value') {
            return $dataDefinition['value'];
        }

        $arguments = [];

        if (isset($dataDefinition['arguments']) && is_array($dataDefinition['arguments'])) {
            $arguments = $dataDefinition['arguments'];
        }

        $command = trim($dataDefinition['command']);

        try {
            return call_user_func_array(array($this->faker, $command), $arguments);
        } catch (InvalidArgumentException $e) {
            throw new InvalidDataDefinitionStructure(
                sprintf('Invalid "Faker" command: %s. %s', $command, $e->getMessage())
            );
        }
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|InvalidDataDefinitionStructure
     */
    private function applyDataToComplexTypeSchema(
        array $decodedSchema,
        array $dataDefinition,
        $predefinedPayload = null
    ) {
        $schemaType = $decodedSchema['type'];

        switch ($schemaType) {
            case AvroSchemaTypes::ARRAY_TYPE:
                $payload = [];

                if ($this->isSimpleSchemaMapping($decodedSchema['items'])) {
                    $payload[] = $this->applyData($dataDefinition);

                    if (is_array($predefinedPayload)) {
                        $payload = array_merge($payload, array_values($predefinedPayload));
                    }

                    break;
                }

                if (!is_array($decodedSchema['items'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Items field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", $this->getSimpleSchemaTypes())
                    ));
                }

                $this->validateComplexSchemaTypeDataDefinition($dataDefinition);

                $payload[] = $this->applyDataToComplexTypeSchema(
                    $decodedSchema['items'],
                    $dataDefinition['definition']
                );

                if (is_array($predefinedPayload)) {
                    array_unshift($predefinedPayload, $payload[0]);

                    $payload = $predefinedPayload;
                }

                break;
            case AvroSchemaTypes::MAP_TYPE:
                $payload = [];

                $this->validateComplexSchemaTypeDataDefinition($dataDefinition);

                if ($this->isSimpleSchemaMapping($decodedSchema['values'])) {
                    foreach ($dataDefinition['definition'] as $definition) {
                        $this->validateDataDefinitionNameField($definition);

                        $payload[$definition['name']] = $this->applyData($definition);
                    }

                    if (is_array($predefinedPayload)) {
                        foreach ($predefinedPayload as $key => $predefinedValue) {
                            if (!is_string($key)) {
                                continue;
                            }

                            $payload[$key] = $predefinedValue;
                        }
                    }

                    break;
                }

                if (!is_array($decodedSchema['values'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Values field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", $this->getSimpleSchemaTypes())
                    ));
                }

                // nas

                break;
            case AvroSchemaTypes::RECORD_TYPE:
                $payload = [];

                // nas

                break;
            default:
                $payload = null;

                $isSchemaTypeSupported = false;

                if (is_array($schemaType)) {
                    if ($schemaType === array_values($schemaType)) {
                        // UNION TYPE
                        // nas
                        $isSchemaTypeSupported = true;
                    }

                    if (isset($schemaType['type'])) {
                        // NESTED SCHEMA
                        // nas
                        $isSchemaTypeSupported = true;
                    }
                }

                if (!$isSchemaTypeSupported) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Schema type "%s" is not supported by Avro.',
                        $schemaType
                    ));
                }
        }

        return $payload;
    }

    /**
     * @param string $schemaType
     * @return bool
     */
    private function isSimpleSchemaMapping(string $schemaType): bool
    {
        return in_array($schemaType, $this->getSimpleSchemaTypes());
    }

    /**
     * @return array
     */
    private function getSimpleSchemaTypes(): array
    {
        return [
            AvroSchemaTypes::NULL_TYPE,
            AvroSchemaTypes::BOOLEAN_TYPE,
            AvroSchemaTypes::INT_TYPE,
            AvroSchemaTypes::LONG_TYPE,
            AvroSchemaTypes::FLOAT_TYPE,
            AvroSchemaTypes::DOUBLE_TYPE,
            AvroSchemaTypes::STRING_TYPE,
            AvroSchemaTypes::ENUM_TYPE
        ];
    }

    /**
     * @param array $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    private function validateSimpleSchemaTypeDataDefinition(array $dataDefinition): void
    {
        if (!isset($dataDefinition['type'])) {
            throw new InvalidDataDefinitionStructure(
                'Each data definition item must contain "type" field.'
            );
        }

        if (!in_array($dataDefinition['type'], ['value' , 'faker'])) {
            throw new InvalidDataDefinitionStructure(
                'Field "type" can contain either "value" or "faker" as value.'
            );
        }

        if ($dataDefinition['type'] === 'value') {
            if (!isset($dataDefinition['value'])) {
                throw new InvalidDataDefinitionStructure(
                    'Item of type "value" must have "value" field.'
                );
            }

            return;
        }

        if (!isset($dataDefinition['command'])) {
            throw new InvalidDataDefinitionStructure(
                'Item of type "faker" must have "command" field.'
            );
        }
    }

    /**
     * @param array $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    private function validateComplexSchemaTypeDataDefinition(array $dataDefinition): void
    {
        if (!isset($dataDefinition['definition']) || !is_array($dataDefinition['definition'])) {
            throw new InvalidDataDefinitionStructure(
                'Data definition item which refers to complex schema type must contain definition field.'
            );
        }
    }

    /**
     * @param array $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    private function validateDataDefinitionNameField(array $dataDefinition): void
    {
        if (!isset($dataDefinition['name']) || trim($dataDefinition['name']) === '') {
            throw new InvalidDataDefinitionStructure(
                'Data definition item must have "name" field.'
            );
        }
    }
}
