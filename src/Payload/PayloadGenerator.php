<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

/**
 * Class PayloadGenerator
 */
class PayloadGenerator implements PayloadGeneratorInterface
{
    /** @var string */
    public const UNSUPPORTED_SCHEMA_TYPE_ERROR_MESSAGE = 'Schema type "%s" is not supported.';

    /**
     * @param string|array<string, mixed> $decodedSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|MissingCommandExecutorException
     */
    public function generate($decodedSchema, SchemaFieldValueResolverInterface $schemaFieldValueResolver)
    {
        if (is_string($decodedSchema)) {
            $decodedSchema = [
                'type' => $decodedSchema
            ];
        } elseif (!isset($decodedSchema['type'])) {
            throw new UnsupportedAvroSchemaTypeException('Schema must contain type attribute.');
        }

        if (false === in_array($decodedSchema['type'], AvroSchemaTypes::getSupportedSchemaTypes())) {
            throw new UnsupportedAvroSchemaTypeException(sprintf(
                self::UNSUPPORTED_SCHEMA_TYPE_ERROR_MESSAGE,
                $decodedSchema['type']
            ));
        }

        return $this->getPayload($decodedSchema, $schemaFieldValueResolver);
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param SchemaFieldValueResolverInterface $schemaFieldValueResolver
     * @param array<integer, string> $path
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|MissingCommandExecutorException
     */
    private function getPayload(
        array $decodedSchema,
        SchemaFieldValueResolverInterface $schemaFieldValueResolver,
        array $path = []
    ) {
        $name = $decodedSchema['name'] ?? 0;

        if (true === in_array($decodedSchema['type'], AvroSchemaTypes::getSimpleSchemaTypes())) {
            return $schemaFieldValueResolver->getValue(
                $decodedSchema['type'],
                $name,
                $path
            );
        }

        if (false === array_key_exists("namespace", $decodedSchema)) {
            $path[] = $name;
        }

        switch ($decodedSchema['type']) {
            case AvroSchemaTypes::RECORD_TYPE:
                $payload = [];

                foreach ($decodedSchema['fields'] as $field) {
                    $payload[$field['name']] = $this->getPayload($field, $schemaFieldValueResolver, $path);
                }

                return $payload;
            case AvroSchemaTypes::ARRAY_TYPE:
                $payload = [];

                $items = $decodedSchema['items'];

                if (!is_array($items)) {
                    $items = [
                        'type' => $items
                    ];
                }

                $payload[] = $this->getPayload($items, $schemaFieldValueResolver, $path);

                return $payload;
            case AvroSchemaTypes::MAP_TYPE:
                $values = $decodedSchema['values'];

                if (!is_array($values)) {
                    $values = [
                        'type' => $values
                    ];
                }

                $key = $schemaFieldValueResolver->getValue(
                    AvroSchemaTypes::STRING_TYPE,
                    0,
                    []
                );

                $payload = [
                    $key => $this->getPayload($values, $schemaFieldValueResolver, $path)
                ];

                return $payload;
            default:
                $payload = null;

                $isSchemaTypeSupported = false;

                if (is_array($decodedSchema['type'])) {
                    if ($decodedSchema['type'] === array_values($decodedSchema['type'])) {
                        // UNION TYPE

                        array_pop($path);

                        $payload = $this->getPayloadFromUnionField(
                            $decodedSchema,
                            $schemaFieldValueResolver,
                            $path
                        );

                        $isSchemaTypeSupported = true;
                    }

                    if (isset($decodedSchema['type']['type'])) {
                        // NESTED SCHEMA

                        $payload = $this->getPayload($decodedSchema['type'], $schemaFieldValueResolver, $path);

                        $isSchemaTypeSupported = true;
                    }
                }

                if (!$isSchemaTypeSupported) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        self::UNSUPPORTED_SCHEMA_TYPE_ERROR_MESSAGE,
                        $decodedSchema['type']
                    ));
                }

                return $payload;
        }
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param SchemaFieldValueResolverInterface $schemaFieldValueResolver
     * @param array<integer, string> $path
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|MissingCommandExecutorException
     */
    private function getPayloadFromUnionField(
        array $decodedSchema,
        SchemaFieldValueResolverInterface $schemaFieldValueResolver,
        array $path
    ) {
        $types = $decodedSchema['type'];

        $extractedPayloads = [];

        foreach ($types as $type) {
            $decodedSchema['type'] = $type;

            $extractedPayloads[] = $this->getPayload($decodedSchema, $schemaFieldValueResolver, $path);
        }

        shuffle($extractedPayloads);

        return $extractedPayloads[0];
    }
}
