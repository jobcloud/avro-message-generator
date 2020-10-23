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
    public function generate(array $decodedSchema, array $dataDefinition, $predefinedPayload)
    {
        if (!isset($decodedSchema['type'])) {
            throw new UnsupportedAvroSchemaTypeException('Schema must contain type attribute.');
        }

        if ($this->isSimpleSchemaMapping($decodedSchema['type'])) {
            $payload = $this->applyData($dataDefinition, $predefinedPayload);

            return $this->applyPredefinedPayload($payload, $predefinedPayload);
        }

        return $this->applyDataToRecordTypeSchema($decodedSchema, $dataDefinition, $predefinedPayload);
    }

    /**
     * @param string $schemaType
     * @return bool
     */
    private function isSimpleSchemaMapping(string $schemaType): bool
    {
        return in_array($schemaType, [
            AvroSchemaTypes::NULL_TYPE,
            AvroSchemaTypes::BOOLEAN_TYPE,
            AvroSchemaTypes::INT_TYPE,
            AvroSchemaTypes::LONG_TYPE,
            AvroSchemaTypes::FLOAT_TYPE,
            AvroSchemaTypes::DOUBLE_TYPE,
            AvroSchemaTypes::STRING_TYPE,
            AvroSchemaTypes::ENUM_TYPE,
            AvroSchemaTypes::ARRAY_TYPE,
            AvroSchemaTypes::MAP_TYPE
        ]) || (is_array($schemaType) && $schemaType === array_values($schemaType)); // union type
    }

    /**
     * @param array<string, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws InvalidDataDefinitionStructure
     */
    private function applyData(array $dataDefinition, $predefinedPayload = null)
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

            return $this->applyPredefinedPayload($dataDefinition['value'], $predefinedPayload);
        }

        if (!isset($dataDefinition['command'])) {
            throw new InvalidDataDefinitionStructure(
                'Item of type "faker" must have "command" field.'
            );
        }

        $arguments = [];

        if (isset($dataDefinition['arguments']) && is_array($dataDefinition['arguments'])) {
            $arguments = $dataDefinition['arguments'];
        }

        try {
            $payload = call_user_func_array(array($this->faker, trim($dataDefinition['command'])), $arguments);

            return $this->applyPredefinedPayload($payload, $predefinedPayload);
        } catch (InvalidArgumentException $e) {
            throw new InvalidDataDefinitionStructure(
                'Invalid "Faker" command. ' . $e->getMessage()
            );
        }
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function applyDataToRecordTypeSchema(array $decodedSchema, array $dataDefinition, $predefinedPayload)
    {
        if ($decodedSchema['type'] !== AvroSchemaTypes::RECORD_TYPE) {
            throw new UnsupportedAvroSchemaTypeException(sprintf(
                'Schema type "%s" is not supported by Avro.',
                $decodedSchema['type']
            ));
        }

        $payload = [];

        // nas
    }

    /**
     * @param mixed $payload
     * @param mixed $predefinedPayload
     * @return mixed
     */
    private function applyPredefinedPayload($payload, $predefinedPayload)
    {
        if (null !== $predefinedPayload) {
            if (is_array($payload) && is_array($predefinedPayload)) {
                return array_merge($payload, $predefinedPayload);
            }

            return $predefinedPayload;
        }

        return $payload;
    }
}
