<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use DateTime;
use Exception;
use InvalidArgumentException;
use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

/**
 * Class PayloadGenerator
 */
class PayloadGenerator implements PayloadGeneratorInterface
{
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
                'Schema type "%s" is not supported.',
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
        if (true === in_array($decodedSchema['type'], AvroSchemaTypes::getSimpleSchemaTypes())) {
            return $schemaFieldValueResolver->getValue(
                $decodedSchema['type'],
                $decodedSchema['name'] ?? null,
                $path
            );
        }

        switch ($decodedSchema['type']) {
            case AvroSchemaTypes::RECORD_TYPE:

                break;
            case AvroSchemaTypes::ARRAY_TYPE:

                break;
            case AvroSchemaTypes::MAP_TYPE:

                break;
            default:

        }

//        switch ($decodedSchema['type']) {
//            case AvroSchemaTypes::NULL_TYPE:
//                $payload = null;
//                break;
//            case AvroSchemaTypes::BOOLEAN_TYPE:
//                $payload = rand(0, 1) == 1;
//                break;
//            case AvroSchemaTypes::INT_TYPE:
//            case AvroSchemaTypes::LONG_TYPE:
//                $payload = rand(1, 100000);
//                break;
//            case AvroSchemaTypes::FLOAT_TYPE:
//            case AvroSchemaTypes::DOUBLE_TYPE:
//                $payload = (float)rand() / (float)getrandmax();
//                break;
//            case AvroSchemaTypes::STRING_TYPE:
//                $payload = $this->faker->word;
//
//                if ($isReadableSchema && isset($decodedSchema['name'])) {
//                    $payload = $this->getReadableStringByFieldName($decodedSchema);
//                }
//
//                break;
//            case AvroSchemaTypes::RECORD_TYPE:
//                $payload = [];
//
//                foreach ($decodedSchema['fields'] as $field) {
//                    $payload[$field['name']] = $this->getPayload($field, null, true);
//                }
//
//                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
//                    $payload = $this->overrideRecordWithPredefinedPayload($payload, $predefinedPayload);
//                }
//
//                break;
//            case AvroSchemaTypes::ENUM_TYPE:
//                $symbols = $decodedSchema['symbols'];
//
//                shuffle($symbols);
//
//                $payload = $symbols[0];
//                break;
//            case AvroSchemaTypes::ARRAY_TYPE:
//                $payload = [];
//
//                $items = $decodedSchema['items'];
//
//                if (!is_array($items)) {
//                    $items = [
//                        'type' => $items
//                    ];
//                }
//
//                $payload[] = $this->getPayload($items, null, true);
//
//                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
//                    $payload = array_merge($payload, array_values($predefinedPayload));
//                }
//                break;
//            case AvroSchemaTypes::MAP_TYPE:
//                $values = $decodedSchema['values'];
//
//                if (!is_array($values)) {
//                    $values = [
//                        'type' => $values
//                    ];
//                }
//
//                $key = $this->faker->word;
//
//                $payload = [
//                    $key => $this->getPayload($values, null, true)
//                ];
//
//                if (null !== $predefinedPayload && is_array($predefinedPayload)) {
//                    foreach ($predefinedPayload as $key => $value) {
//                        $castKey = (string)$key;
//
//                        $payload[$castKey] = $value;
//                    }
//                }
//                break;
//            default:
//                $payload = null;
//
//                $isSchemaTypeSupported = false;
//
//                if (is_array($decodedSchema['type'])) {
//                    if ($decodedSchema['type'] === array_values($decodedSchema['type'])) {
//                        // UNION TYPE
//                        if (null !== $predefinedPayload) {
//                            return $predefinedPayload;
//                        }
//
//                        $payload = $this->extractPayloadFromUnionField($decodedSchema);
//
//                        $isSchemaTypeSupported = true;
//                    }
//
//                    if (isset($decodedSchema['type']['type'])) {
//                        // NESTED SCHEMA
//                        $payload = $this->getPayload($decodedSchema['type'], null, true);
//
//                        $isSchemaTypeSupported = true;
//                    }
//                }
//
//                if (!$isSchemaTypeSupported) {
//                    throw new UnsupportedAvroSchemaTypeException(sprintf(
//                        'Schema type "%s" is not supported by Avro.',
//                        $decodedSchema['type']
//                    ));
//                }
//        }

        return $payload;
    }

//    /**
//     * @param array<string, mixed> $decodedSchema
//     * @return string
//     */
//    private function getReadableStringByFieldName(array $decodedSchema): string
//    {
//        $fieldName = $decodedSchema['name'];
//
//        if ($fieldName === 'id' || mb_substr($fieldName, -2) === 'Id') {
//            return $this->faker->uuid;
//        }
//
//        if (mb_substr($fieldName, -2) === 'At') {
//            $daysCount = $this->days;
//
//            $this->days += $this->dateTimeFieldsStepInDays;
//
//            try {
//                return (new DateTime('+' . $daysCount . ' days'))->format(DateTime::ATOM);
//            } catch (Exception $e) {
//                return (new DateTime())->format(DateTime::ATOM);
//            }
//        }
//
//        $predefinedOption = $this->extractPredefinedOptionFromDocField($decodedSchema);
//
//        if ('' !== $predefinedOption) {
//            return $predefinedOption;
//        }
//
//        try {
//            return (string)$this->faker->{$fieldName};
//        } catch (InvalidArgumentException $e) {
//            if ($fieldName === 'language') {
//                return $this->faker->languageCode;
//            }
//
//            if ($fieldName === 'postalCode') {
//                return $this->faker->postcode;
//            }
//
//            if ($fieldName === 'street') {
//                return $this->faker->streetName;
//            }
//        }
//
//        return $this->faker->word;
//    }
//
//    /**
//     * @param array<string, mixed> $decodedSchema
//     * @return string
//     */
//    private function extractPredefinedOptionFromDocField(array $decodedSchema): string
//    {
//        if (!isset($decodedSchema['doc'])) {
//            return '';
//        }
//
//        $doc = $decodedSchema['doc'];
//
//        if (false !== mb_strpos($doc, "ISO-8601")) {
//            $daysCount = $this->days;
//
//            $this->days += $this->dateTimeFieldsStepInDays;
//
//            try {
//                return (new DateTime('+' . $daysCount . ' days'))->format(DateTime::ATOM);
//            } catch (Exception $e) {
//                return (new DateTime())->format(DateTime::ATOM);
//            }
//        }
//
//        $openBracketPosition = mb_strpos($doc, '[');
//
//        $closeBracketPosition = mb_strpos($doc, ']');
//
//        if (false === $openBracketPosition || false === $closeBracketPosition) {
//            return '';
//        }
//
//        if ($openBracketPosition > $closeBracketPosition) {
//            return '';
//        }
//
//        $options = array_map(
//            fn($item) => trim($item, "[ ']"),
//            explode(",", mb_substr($doc, $openBracketPosition + 1, $closeBracketPosition - $openBracketPosition - 1))
//        );
//
//        shuffle($options);
//
//        return $options[0];
//    }
//
//    /**
//     * @param array<string, mixed> $decodedSchema
//     * @return mixed
//     * @throws UnsupportedAvroSchemaTypeException
//     */
//    private function extractPayloadFromUnionField(array $decodedSchema)
//    {
//        $types = $decodedSchema['type'];
//
//        $extractedPayloads = [];
//
//        foreach ($types as $type) {
//            $decodedSchema['type'] = $type;
//
//            $extractedPayloads[] = $this->getPayload($decodedSchema, null, true);
//        }
//
//        shuffle($extractedPayloads);
//
//        return $extractedPayloads[0];
//    }
//
//    /**
//     * @param array<int|string, mixed> $payload
//     * @param array<string, mixed> $predefinedPayload
//     * @return array<int|string, mixed>
//     */
//    private function overrideRecordWithPredefinedPayload(array $payload, array $predefinedPayload): array
//    {
//        if ([] === $predefinedPayload) {
//            return $payload;
//        }
//
//        foreach ($predefinedPayload as $fieldName => $fieldValue) {
//            if (is_array($fieldValue) && isset($payload[$fieldName]) && is_array($payload[$fieldName])) {
//                $payload[$fieldName] = $this->overrideRecordWithPredefinedPayload($payload[$fieldName], $fieldValue);
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
