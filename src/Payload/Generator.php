<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Exception;
use Faker\Generator as Faker;
use InvalidArgumentException;
use Jobcloud\Avro\Message\Generator\DataDefinition\ValidatorInterface as DataDefinitionValidatorInterface;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionStructureException;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private Faker $faker;

    private DataDefinitionValidatorInterface $dataDefinitionValidator;

    /**
     * @param Faker $faker
     * @param DataDefinitionValidatorInterface $dataDefinitionValidator
     */
    public function __construct(
        Faker $faker,
        DataDefinitionValidatorInterface $dataDefinitionValidator
    ) {
        $this->faker = $faker;
        $this->dataDefinitionValidator = $dataDefinitionValidator;
    }

    /**
     * @param string|array<string, mixed> $decodedSchema
     * @param array<string|integer, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|InvalidDataDefinitionStructureException
     */
    public function generate($decodedSchema, array $dataDefinition, $predefinedPayload = null)
    {
        if (is_string($decodedSchema)) {
            if (!in_array($decodedSchema, AvroSchemaTypes::getSimpleSchemaTypes())) {
                throw new UnsupportedAvroSchemaTypeException(sprintf(
                    'Schema type "%s" is not supported by Avro.',
                    $decodedSchema
                ));
            }

            return $this->applyDataToSimpleSchemaType($dataDefinition, $predefinedPayload);
        }

        if (!isset($decodedSchema['type'])) {
            throw new UnsupportedAvroSchemaTypeException('Schema must contain type attribute.');
        }

        if (in_array($decodedSchema['type'], AvroSchemaTypes::getSimpleSchemaTypes())) {
            return $this->applyDataToSimpleSchemaType($dataDefinition, $predefinedPayload);
        }

        return $this->applyDataToComplexSchemaType($decodedSchema, $dataDefinition, $predefinedPayload);
    }

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @return mixed
     * @throws InvalidDataDefinitionStructureException
     */
    private function applyData(array $dataDefinition)
    {
        $this->dataDefinitionValidator->validateSimpleSchemaTypeDataDefinition($dataDefinition);

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
            throw new InvalidDataDefinitionStructureException(
                sprintf('Invalid "Faker" command: %s. %s', $command, $e->getMessage())
            );
        }
    }

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws InvalidDataDefinitionStructureException
     */
    private function applyDataToSimpleSchemaType(array $dataDefinition, $predefinedPayload)
    {
        if (null !== $predefinedPayload) {
            return $predefinedPayload;
        }

        return $this->applyData($dataDefinition);
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string|integer, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|InvalidDataDefinitionStructureException
     */
    private function applyDataToComplexSchemaType(
        array $decodedSchema,
        array $dataDefinition,
        $predefinedPayload = null
    ) {
        $schemaType = $decodedSchema['type'];

        switch ($schemaType) {
            case AvroSchemaTypes::ARRAY_TYPE:
                $payload = [];

                $isSimpleSchemaMapping = in_array($decodedSchema['items'], AvroSchemaTypes::getSimpleSchemaTypes());

                if (!$isSimpleSchemaMapping && !is_array($decodedSchema['items'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Items field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", AvroSchemaTypes::getSimpleSchemaTypes())
                    ));
                }

                foreach ($dataDefinition as $definition) {
                    if ($isSimpleSchemaMapping) {
                        $payload[] = $this->applyData($definition);

                        continue;
                    }

                    $this->dataDefinitionValidator->validateComplexSchemaTypeDataDefinition($definition);

                    $payload[] = $this->applyDataToComplexSchemaType(
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

                $isSimpleSchemaMapping = in_array($decodedSchema['values'], AvroSchemaTypes::getSimpleSchemaTypes());

                if (!$isSimpleSchemaMapping && !is_array($decodedSchema['values'])) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Values field of schema type "%s" must contain either nested schema or one of values: %s',
                        $decodedSchema['type'],
                        implode(", ", AvroSchemaTypes::getSimpleSchemaTypes())
                    ));
                }

                foreach ($dataDefinition as $definition) {
                    $this->dataDefinitionValidator->validateDataDefinitionNameField($definition, $schemaType);

                    if ($isSimpleSchemaMapping) {
                        $payload[$definition['name']] = $this->applyData($definition);

                        continue;
                    }

                    $this->dataDefinitionValidator->validateComplexSchemaTypeDataDefinition($definition);

                    $payload[$definition['name']] = $this->applyDataToComplexSchemaType(
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
                    $this->dataDefinitionValidator->validateDataDefinitionNameField($definition, $schemaType);

                    $mappedDataDefinitionsByName[$definition['name']] = $definition;
                }

                foreach ($decodedSchema['fields'] as $field) {
                    $fieldName = $field['name'];

                    $this->dataDefinitionValidator->validateDataDefinitionByName(
                        $mappedDataDefinitionsByName,
                        $fieldName
                    );

                    $definition = $mappedDataDefinitionsByName[$fieldName];

                    $isSimpleSchemaMapping = in_array($field['type'], AvroSchemaTypes::getSimpleSchemaTypes());

                    if ($isSimpleSchemaMapping) {
                        $payload[$fieldName] = $this->applyData($definition);

                        continue;
                    }

                    if (isset($definition['definitions'])) {
                        $definition = $definition['definitions'];
                    }

                    $payload[$fieldName] = $this->applyDataToComplexSchemaType(
                        $field,
                        $definition
                    );
                }

                if (is_array($predefinedPayload)) {
                    $payload = $this->overrideRecordWithPredefinedPayload($payload, $predefinedPayload);
                }

                break;
            default:
                $payload = null;

                $isSchemaTypeSupported = false;

                if ($this->isNestedSchemaType($schemaType)) {
                    // NESTED SCHEMA
                    $payload = $this->applyDataToComplexSchemaType($schemaType, $dataDefinition);

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
                } elseif ($this->isUnionSchemaType($schemaType)) {
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
     * @throws InvalidDataDefinitionStructureException
     */
    private function resolveUnionPayloadForComplexSchemaType(
        array $schemaTypes,
        array $definitions
    ) {
        /** @var array<string, mixed> $schemaType */
        foreach ($schemaTypes as $schemaType) {
            try {
                return $this->applyDataToComplexSchemaType($schemaType, $definitions);
            } catch (Exception $e) {
                continue;
            }
        }

        throw new InvalidDataDefinitionStructureException('Invalid "definitions" applied for "Union" schema type.');
    }

    /**
     * @param mixed $schemaType
     * @return bool
     */
    private function isUnionSchemaType($schemaType): bool
    {
        if (false === is_array($schemaType)) {
            return false;
        }

        if ([] === $schemaType) {
            return false;
        }

        if ($schemaType !== array_values($schemaType)) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $schemaType
     * @return bool
     */
    private function isNestedSchemaType($schemaType): bool
    {
        if (false === is_array($schemaType)) {
            return false;
        }

        if (false === array_key_exists('type', $schemaType)) {
            return false;
        }

        return true;
    }
}
