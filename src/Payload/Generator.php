<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Faker\Generator as Faker;
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
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    public function generate(array $decodedSchema, $predefinedPayload)
    {
        if (!isset($decodedSchema['type'])) {
            throw new UnsupportedAvroSchemaTypeException('Schema must contain type attribute.');
        }

        if (null !== $predefinedPayload && in_array($decodedSchema['type'], $this->getSimpleOverridableTypes())) {
            return $predefinedPayload;
        }

        return $this->getPayload($decodedSchema, $predefinedPayload);
    }

    /**
     * @return array<integer, string>
     */
    private function getSimpleOverridableTypes(): array
    {
        return [
            AvroSchemaTypes::BOOLEAN_TYPE,
            AvroSchemaTypes::INT_TYPE,
            AvroSchemaTypes::LONG_TYPE,
            AvroSchemaTypes::FLOAT_TYPE,
            AvroSchemaTypes::DOUBLE_TYPE,
            AvroSchemaTypes::BYTES_TYPE,
            AvroSchemaTypes::STRING_TYPE
        ];
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function getPayload(array $decodedSchema, $predefinedPayload = null)
    {
        switch ($decodedSchema['type']) {
            case AvroSchemaTypes::NULL_TYPE:
                $payload = null;
                break;
            case AvroSchemaTypes::BOOLEAN_TYPE:
                $payload = rand(0, 1) == 1;
                break;
            case AvroSchemaTypes::INT_TYPE:
                $payload = rand(1, 10000);
                break;
            case AvroSchemaTypes::LONG_TYPE:
                // nas
                break;
            case AvroSchemaTypes::FLOAT_TYPE:

                break;
            case AvroSchemaTypes::DOUBLE_TYPE:

                break;
            case AvroSchemaTypes::BYTES_TYPE:

                break;
            case AvroSchemaTypes::STRING_TYPE:

                break;
            case AvroSchemaTypes::RECORD_TYPE:

                break;
            case AvroSchemaTypes::ENUM_TYPE:

                break;
            case AvroSchemaTypes::ARRAY_TYPE:

                break;
            case AvroSchemaTypes::MAP_TYPE:

                break;
            case AvroSchemaTypes::FIXED_TYPE:// nas check this and enum

                break;
            default:
                throw new UnsupportedAvroSchemaTypeException(sprintf(
                    'Schema type "%s" is not supported by Avro.',
                    $decodedSchema['type']
                ));
        }

        return $payload;
    }
//
//    /**
//     * @param array $payload
//     * @param array $predefinedPayload
//     * @return array
//     */
//    private function overrideWithPredefinedPayload(array $payload, array $predefinedPayload): array
//    {
//        if ([] === $predefinedPayload) {
//            return $payload;
//        }
//
//        foreach ($predefinedPayload as $fieldName => $fieldValue) {
//            if (is_array($fieldValue) && isset($payload[$fieldName]) && is_array($payload[$fieldName])) {
//                $payload[$fieldName] = $this->overrideWithPredefinedPayload($payload[$fieldName], $fieldValue);
//
//                continue;
//            }
//
//            $payload[$fieldName] = $fieldValue;
//        }
//
//        return $payload;
//    }
}
