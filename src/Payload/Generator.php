<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use DateTime;
use Faker\Generator as Faker;
use InvalidArgumentException;
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
     * @param bool $isReadableSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function getPayload(array $decodedSchema, $predefinedPayload = null, bool $isReadableSchema = false)
    {
        switch ($decodedSchema['type']) {
            case AvroSchemaTypes::NULL_TYPE:
                $payload = null;
                break;
            case AvroSchemaTypes::BOOLEAN_TYPE:
                $payload = rand(0, 1) == 1;
                break;
            case AvroSchemaTypes::INT_TYPE:
            case AvroSchemaTypes::LONG_TYPE:
                $payload = rand(1, 100000);
                break;
            case AvroSchemaTypes::FLOAT_TYPE:
            case AvroSchemaTypes::DOUBLE_TYPE:
                $payload = (float) rand() / (float) getrandmax();
                break;
            case AvroSchemaTypes::BYTES_TYPE:
                $payload = pack('C', rand(1, 100));
                break;
            case AvroSchemaTypes::STRING_TYPE:
                $payload = $this->faker->word;

                if ($isReadableSchema && isset($decodedSchema['name'])) {
                    $payload = $this->getReadableStringByFieldName($decodedSchema);
                }

                break;
            case AvroSchemaTypes::RECORD_TYPE:

                $payload = [];

                foreach ($decodedSchema['fields'] as $field) {
                    $payload[$field['name']] = $this->getPayload($field);
                }

                break;
            case AvroSchemaTypes::ENUM_TYPE:

                break;
            case AvroSchemaTypes::ARRAY_TYPE:

                break;
            case AvroSchemaTypes::MAP_TYPE:

                break;
            case AvroSchemaTypes::FIXED_TYPE:// nas check this

                break;
            default:
                if (is_array($decodedSchema['type'])) {
                    if ($decodedSchema['type'] === array_values($decodedSchema['type'])) {
                        // UNION TYPE
                        $payload = $this->extractPayloadFromUnionField($decodedSchema);
                    }

                    if (isset($decodedSchema['type']['type'])) {
                        // NESTED SCHEMA
                        $payload = $this->getPayload($decodedSchema['type'], null, true);
                    }
                }

                if (!isset($payload)) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Schema type "%s" is not supported by Avro.',
                        $decodedSchema['type']
                    ));
                }
        }

        return $payload;
    }

    /**
     * @param array $decodedSchema
     * @return string
     */
    private function getReadableStringByFieldName(array $decodedSchema): string
    {
        $fieldName = $decodedSchema['name'];

        try {
            return (string) $this->faker->{$fieldName};
        } catch (InvalidArgumentException $e) {
            if ($fieldName === 'id' || mb_substr($fieldName, -2) === 'Id') {
                return $this->faker->uuid;
            }

            if (mb_substr($fieldName, -2) === 'At') {
                return (new DateTime())->format(DateTime::ATOM);
            }

            $predefinedOption = $this->extractPredefinedOptionFromDocField($decodedSchema);

            if ('' !== $predefinedOption) {
                return $predefinedOption;
            }

            if ($fieldName === 'language') {
                return $this->faker->languageCode;
            }

            if ($fieldName === 'postalCode') {
                return $this->faker->postcode;
            }

            if ($fieldName === 'street') {
                return $this->faker->streetName;
            }
        }

        return $this->faker->word;
    }

    /**
     * @param array $decodedSchema
     * @return string
     */
    private function extractPredefinedOptionFromDocField(array $decodedSchema): string
    {
        if (!isset($decodedSchema['doc'])) {
            return '';
        }

        $doc = $decodedSchema['doc'];

        if (false !== mb_strpos($doc, "ISO-8601")) {
            return (new DateTime())->format(DateTime::ATOM);
        }

        $openBracketPosition = mb_strpos($doc, '[');

        $closeBracketPosition = mb_strpos($doc, ']');

        if (false === $openBracketPosition || false === $closeBracketPosition) {
            return '';
        }

        if ($openBracketPosition > $closeBracketPosition) {
            return '';
        }

        $options = array_map(
            fn($item) => trim($item, "[ ']"),
            explode(",", mb_substr($doc, $openBracketPosition + 1, $closeBracketPosition - $openBracketPosition - 1))
        );

        shuffle($options);

        return $options[0];
    }

    /**
     * @param array $decodedSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    private function extractPayloadFromUnionField(array $decodedSchema)
    {
        $types = $decodedSchema['type'];

        $extractedPayloads = [];

        foreach ($types as $type) {
            $decodedSchema['type'] = $type;

            $extractedPayloads[] = $this->getPayload($decodedSchema, null, true);
        }

        shuffle($extractedPayloads);

        return $extractedPayloads[0];
    }
//        if ($fieldName === 'url') {
//            return $this->faker->url;
//        }
//
//        if ($fieldName === 'text') {
//            return $this->faker->text;
//        }
//
//        if ($fieldName === 'email') {
//            return $this->faker->email;
//        }
//
//        if ($fieldName === 'name') {
//            return $this->faker->name;
//        }
//
//        if ($fieldName === 'title') {
//            return $this->faker->title;
//        }
//
//        if ($fieldName === 'firstName') {
//            return $this->faker->firstName;
//        }
//
//        if ($fieldName === 'lastName') {
//            return $this->faker->lastName;
//        }
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
