<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Exception;
use Faker\Generator as Faker;
use InvalidArgumentException;
use Jobcloud\Avro\Message\Generator\DataDefinition\ValidatorInterface;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionStructure;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private Faker $faker;

    private ValidatorInterface $validator;

    /**
     * @param Faker $faker
     * @param ValidatorInterface $validator
     */
    public function __construct(
        Faker $faker,
        ValidatorInterface $validator
    ) {
        $this->faker = $faker;
        $this->validator = $validator;
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string|integer, mixed> $dataDefinition
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
     * @param array<string|integer, mixed> $dataDefinition
     * @return mixed
     * @throws InvalidDataDefinitionStructure
     */
    private function applyData(array $dataDefinition)
    {
        $this->validator->validateSimpleSchemaTypeDataDefinition($dataDefinition);

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
     * @param array<string|integer, mixed> $dataDefinition
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

                $isSimpleSchemaMapping = $this->isSimpleSchemaMapping($decodedSchema['items']);

                if (!$isSimpleSchemaMapping && !is_array($decodedSchema['items'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Items field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", $this->getSimpleSchemaTypes())
                    ));
                }

                foreach ($dataDefinition as $definition) {
                    if ($isSimpleSchemaMapping) {
                        $payload[] = $this->applyData($definition);

                        continue;
                    }

                    $this->validator->validateComplexSchemaTypeDataDefinition($definition);

                    $payload[] = $this->applyDataToComplexTypeSchema(
                        $decodedSchema['items'],
                        $definition['definitions']
                    );
                }

                if (is_array($predefinedPayload)) {
                    $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                        $payload,
                        $predefinedPayload,
                        'is_integer'
                    );
                }

                break;
            case AvroSchemaTypes::MAP_TYPE:
                $payload = [];

                $isSimpleSchemaMapping = $this->isSimpleSchemaMapping($decodedSchema['values']);

                if (!$isSimpleSchemaMapping && !is_array($decodedSchema['values'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Values field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", $this->getSimpleSchemaTypes())
                    ));
                }

                foreach ($dataDefinition as $definition) {
                    $this->validator->validateDataDefinitionNameField($definition, $schemaType);

                    if ($isSimpleSchemaMapping) {
                        $payload[$definition['name']] = $this->applyData($definition);

                        continue;
                    }

                    $this->validator->validateComplexSchemaTypeDataDefinition($definition);

                    $payload[$definition['name']] = $this->applyDataToComplexTypeSchema(
                        $decodedSchema['values'],
                        $definition['definitions']
                    );
                }

                if (is_array($predefinedPayload)) {
                    $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                        $payload,
                        $predefinedPayload,
                        'is_string'
                    );
                }

                break;
            case AvroSchemaTypes::RECORD_TYPE:
                $payload = [];

                $mappedDataDefinitionsByName = [];

                foreach ($dataDefinition as $definition) {
                    $this->validator->validateDataDefinitionNameField($definition, $schemaType);

                    $mappedDataDefinitionsByName[$definition['name']] = $definition;
                }

                foreach ($decodedSchema['fields'] as $field) {
                    $fieldName = $field['name'];

                    $this->validator->validateDataDefinitionByName($mappedDataDefinitionsByName, $fieldName);

                    $definition = $mappedDataDefinitionsByName[$fieldName];

                    $isSimpleSchemaMapping = $this->isSimpleSchemaMapping($field['type']);

                    if ($isSimpleSchemaMapping) {
                        $payload[$fieldName] = $this->applyData($definition);

                        continue;
                    }

                    $this->validator->validateComplexSchemaTypeDataDefinition($definition);

                    $payload[$fieldName] = $this->applyDataToComplexTypeSchema(
                        $field,
                        $definition['definitions']
                    );
                }

                if (is_array($predefinedPayload)) {
                    $payload = $this->overrideRecordWithPredefinedPayload($payload, $predefinedPayload);
                }

                break;
            default:
                $payload = null;

                $isSchemaTypeSupported = false;

                if (is_array($schemaType) && [] !== $schemaType) {
                    if ($schemaType === array_values($schemaType)) {
                        // UNION TYPE
                        if (isset($dataDefinition['type'])) { // simple schema type
                            if (null !== $predefinedPayload) {
                                return $predefinedPayload;
                            }

                            $payload = $this->applyData($dataDefinition);
                        } elseif (isset($dataDefinition['definitions'])) { // complex schema type
                            $payload = $this->resolveUnionPayloadForComplexSchemaType(
                                $schemaType,
                                $dataDefinition['definitions']
                            );

                            if (null !== $predefinedPayload) {
                                if (is_array($payload)) {
                                    if (is_integer(array_keys($payload)[0])) {
                                        $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                                            $payload,
                                            $predefinedPayload,
                                            'is_integer'
                                        );
                                    } else {
                                        $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                                            $payload,
                                            $predefinedPayload,
                                            'is_string'
                                        );
                                    }
                                } else {
                                    $payload = $predefinedPayload;
                                }
                            }
                        }

                        $isSchemaTypeSupported = true;
                    }

                    if (isset($schemaType['type'])) {
                        // NESTED SCHEMA
                        $payload = $this->applyDataToComplexTypeSchema($schemaType, $dataDefinition);

                        if (null !== $predefinedPayload) {
                            if (is_integer(array_keys($payload)[0])) {
                                $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                                    $payload,
                                    $predefinedPayload,
                                    'is_integer'
                                );
                            } else {
                                $payload = $this->overrideArrayPayloadWithPredefinedPayload(
                                    $payload,
                                    $predefinedPayload,
                                    'is_string'
                                );
                            }
                        }

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
     * @return array<integer, string>
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
     * @param array<int|string, mixed> $payload
     * @param array<string, mixed> $predefinedPayload
     * @return array<int|string, mixed>
     */
    private function overrideRecordWithPredefinedPayload(array $payload, array $predefinedPayload): array
    {
        if ([] === $predefinedPayload) {
            return $payload;
        }

        foreach ($predefinedPayload as $fieldName => $fieldValue) {
            if (is_array($fieldValue) && isset($payload[$fieldName]) && is_array($payload[$fieldName])) {
                $payload[$fieldName] = $this->overrideRecordWithPredefinedPayload($payload[$fieldName], $fieldValue);

                continue;
            }

            $payload[$fieldName] = $fieldValue;
        }

        return $payload;
    }

    /**
     * @param array<string|integer, mixed> $payload
     * @param array<string|integer, mixed> $predefinedPayload
     * @param callable $keyTypeCheckerCallback
     * @return array<string|integer, mixed>
     */
    private function overrideArrayPayloadWithPredefinedPayload(
        array $payload,
        array $predefinedPayload,
        callable $keyTypeCheckerCallback
    ): array {
        foreach ($predefinedPayload as $key => $data) {
            if ($keyTypeCheckerCallback($key)) {
                $payload[$key] = $data;
            }
        }

        return $payload;
    }

    /**
     * @param array<integer, array<string, mixed>> $schemaTypes
     * @param array<integer, mixed> $definitions
     * @return mixed
     * @throws InvalidDataDefinitionStructure
     */
    private function resolveUnionPayloadForComplexSchemaType(
        array $schemaTypes,
        array $definitions
    ) {
        /** @var array<string, mixed> $schemaType */
        foreach ($schemaTypes as $schemaType) {
            try {
                return $this->applyDataToComplexTypeSchema($schemaType, $definitions);
            } catch (Exception $e) {}
        }

        throw new InvalidDataDefinitionStructure('Invalid "definitions" applied for "Union" schema type.');
    }
}
