<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

class PayloadGenerator implements PayloadGeneratorInterface
{
    private SchemaFieldValueResolverInterface $schemaFieldValueResolver;

    /**
     * @param SchemaFieldValueResolverInterface $schemaFieldValueResolver
     */
    public function __construct(SchemaFieldValueResolverInterface $schemaFieldValueResolver)
    {
        $this->schemaFieldValueResolver = $schemaFieldValueResolver;
    }

    /**
     * @param string|array<string, mixed> $decodedSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    public function generate($decodedSchema)
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
                'Schema type "%s" is not supported.',
                $decodedSchema['type']
            ));
        }

        return $this->getPayload($decodedSchema, [], true);
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<integer, string> $path
     * @param bool $isRootSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function getPayload(
        array $decodedSchema,
        array $path = [],
        bool $isRootSchema = false
    ) {
        if (true === in_array($decodedSchema['type'], AvroSchemaTypes::getSimpleSchemaTypes())) {
            return $this->schemaFieldValueResolver->getValue(
                $decodedSchema,
                $path,
                $isRootSchema
            );
        }

        $name = $decodedSchema['name'] ?? 0;

        if (false === $isRootSchema) {
            $path[] = $name;
        }

        switch ($decodedSchema['type']) {
            case AvroSchemaTypes::RECORD_TYPE:
                $payload = [];

                foreach ($decodedSchema['fields'] as $field) {
                    $payload[$field['name']] = $this->getPayload($field, $path);
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

                $payload[] = $this->getPayload($items, $path);

                return $payload;
            case AvroSchemaTypes::MAP_TYPE:
                $values = $decodedSchema['values'];

                if (!is_array($values)) {
                    $values = [
                        'type' => $values
                    ];
                }

                $mapKeyPath = [];

                if ($isRootSchema) {
                    // force resolver to use Faker for key generation
                    $fakeKeyPath = base64_encode('fakeKey');

                    $mapKeyPath[] = $fakeKeyPath;
                }

                $key = $this->schemaFieldValueResolver->getValue(
                    [
                        'type' => AvroSchemaTypes::STRING_TYPE
                    ],
                    $mapKeyPath
                );

                $payload = [
                    $key => $this->getPayload($values, $path)
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
                            $path
                        );

                        $isSchemaTypeSupported = true;
                    }

                    if (isset($decodedSchema['type']['type'])) {
                        // NESTED SCHEMA

                        $payload = $this->getPayload($decodedSchema['type'], $path);

                        $isSchemaTypeSupported = true;
                    }
                }

                if (!$isSchemaTypeSupported) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Nested schema type "%s" is not supported.',
                        $decodedSchema['type']
                    ));
                }

                return $payload;
        }
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<integer, string> $path
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function getPayloadFromUnionField(
        array $decodedSchema,
        array $path
    ) {
        $types = $decodedSchema['type'];

        $extractedPayloads = [];

        foreach ($types as $type) {
            $decodedSchema['type'] = $type;

            $extractedPayloads[] = $this->getPayload($decodedSchema, $path);
        }

        shuffle($extractedPayloads);

        return $extractedPayloads[0];
    }
}
