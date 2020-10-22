<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use DateTime;
use Exception;
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

    private int $fixedSizeLimit;

    private int $dateTimeFieldsStepInDays;

    private int $days;

    /**
     * @param Faker $faker
     * @param int $fixedSizeLimit
     * @param int $dateTimeFieldsStepInDays
     */
    public function __construct(Faker $faker, int $fixedSizeLimit = 1024, int $dateTimeFieldsStepInDays = 1)
    {
        $this->faker = $faker;
        $this->fixedSizeLimit = $fixedSizeLimit;
        $this->dateTimeFieldsStepInDays = $dateTimeFieldsStepInDays;
        $this->days = $dateTimeFieldsStepInDays;
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

        $this->days = $this->dateTimeFieldsStepInDays;

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
            AvroSchemaTypes::STRING_TYPE,
            AvroSchemaTypes::FIXED_TYPE,
            AvroSchemaTypes::ENUM_TYPE
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
                    $payload[$field['name']] = $this->getPayload($field, null, true);
                }

                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
                    $payload = $this->overrideRecordWithPredefinedPayload($payload, $predefinedPayload);
                }

                break;
            case AvroSchemaTypes::ENUM_TYPE:
                $symbols = $decodedSchema['symbols'];

                shuffle($symbols);

                $payload = $symbols[0];
                break;
            case AvroSchemaTypes::ARRAY_TYPE:
                $payload = [];

                $items = $decodedSchema['items'];

                if (!is_array($items)) {
                    $items = [
                        'type' => $items
                    ];
                }

                $payload[] = $this->getPayload($items, null, true);

                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
                    $payload = array_merge($payload, array_values($predefinedPayload));
                }
                break;
            case AvroSchemaTypes::MAP_TYPE:
                $values = $decodedSchema['values'];

                if (!is_array($values)) {
                    $values = [
                        'type' => $values
                    ];
                }

                $key = $this->faker->word;

                $payload = [
                    $key => $this->getPayload($values, null, true)
                ];

                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
                    foreach ($predefinedPayload as $key => $value) {
                        $castKey = (string) $key;

                        $payload[$castKey] = $value;
                    }
                }
                break;
            case AvroSchemaTypes::FIXED_TYPE:
                $size = $decodedSchema['size'];

                if ($size > $this->fixedSizeLimit) {
                    $size = $this->fixedSizeLimit;
                }

                $payload = bin2hex(random_bytes($size));
                break;
            default:
                $payload = null;

                $isSchemaTypeSupported = false;

                if (is_array($decodedSchema['type'])) {
                    if ($decodedSchema['type'] === array_values($decodedSchema['type'])) {
                        // UNION TYPE
                        if (null !== $predefinedPayload) {
                            return $predefinedPayload;
                        }

                        $payload = $this->extractPayloadFromUnionField($decodedSchema);

                        $isSchemaTypeSupported = true;
                    }

                    if (isset($decodedSchema['type']['type'])) {
                        // NESTED SCHEMA
                        $payload = $this->getPayload($decodedSchema['type'], null, true);

                        $isSchemaTypeSupported = true;
                    }
                }

                if (!$isSchemaTypeSupported) {
                    throw new UnsupportedAvroSchemaTypeException(sprintf(
                        'Schema type "%s" is not supported by Avro.',
                        $decodedSchema['type']
                    ));
                }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $decodedSchema
     * @return string
     */
    private function getReadableStringByFieldName(array $decodedSchema): string
    {
        $fieldName = $decodedSchema['name'];

        if ($fieldName === 'id' || mb_substr($fieldName, -2) === 'Id') {
            return $this->faker->uuid;
        }

        if (mb_substr($fieldName, -2) === 'At') {
            $daysCount = $this->days;

            $this->days += $this->dateTimeFieldsStepInDays;

            try {
                return (new DateTime('+' . $daysCount . ' days'))->format(DateTime::ATOM);
            } catch (Exception $e) {
                return (new DateTime())->format(DateTime::ATOM);
            }
        }

        $predefinedOption = $this->extractPredefinedOptionFromDocField($decodedSchema);

        if ('' !== $predefinedOption) {
            return $predefinedOption;
        }

        try {
            return (string) $this->faker->{$fieldName};
        } catch (InvalidArgumentException $e) {
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
     * @param array<string, mixed> $decodedSchema
     * @return string
     */
    private function extractPredefinedOptionFromDocField(array $decodedSchema): string
    {
        if (!isset($decodedSchema['doc'])) {
            return '';
        }

        $doc = $decodedSchema['doc'];

        if (false !== mb_strpos($doc, "ISO-8601")) {
            $daysCount = $this->days;

            $this->days += $this->dateTimeFieldsStepInDays;

            try {
                return (new DateTime('+' . $daysCount . ' days'))->format(DateTime::ATOM);
            } catch (Exception $e) {
                return (new DateTime())->format(DateTime::ATOM);
            }
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
     * @param array<string, mixed> $decodedSchema
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
}
