<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;

/**
 * Class DataDefinitionFieldValidator
 */
class DataDefinitionFieldValidator implements DataDefinitionFieldValidatorInterface
{
    /** @var array<integer, string> */
    private const SUPPORTED_FIELDS = [
        DataDefinitionField::VALUE_FIELD,
        DataDefinitionField::COMMAND_FIELD,
        DataDefinitionField::ARGUMENTS_FIELD
    ];

    /**
     * @param array<string|integer, mixed> $decodedDataDefinitionField
     * @return void
     * @throws InvalidDataDefinitionFieldException
     */
    public function validateDataDefinitionField(array $decodedDataDefinitionField): void
    {
        if (array_diff(array_keys($decodedDataDefinitionField), self::SUPPORTED_FIELDS) !== []) {
            throw new InvalidDataDefinitionFieldException(
                sprintf(
                    'Data definition field can contain following fields: %s.',
                    implode(", ", self::SUPPORTED_FIELDS)
                )
            );
        }

        if (array_key_exists(DataDefinitionField::VALUE_FIELD, $decodedDataDefinitionField)) {
            if (count($decodedDataDefinitionField) !== 1) {
                throw new InvalidDataDefinitionFieldException(
                    sprintf(
                        'Data definition field of type "%s" can not contain other fields.',
                        DataDefinitionField::VALUE_FIELD
                    )
                );
            }

            return;
        }

        if (array_key_exists(DataDefinitionField::COMMAND_FIELD, $decodedDataDefinitionField)) {
            if (
                !is_string($decodedDataDefinitionField[DataDefinitionField::COMMAND_FIELD]) ||
                '' === trim($decodedDataDefinitionField[DataDefinitionField::COMMAND_FIELD])
            ) {
                throw new InvalidDataDefinitionFieldException(
                    sprintf('Data definition field "%s" must be string.', DataDefinitionField::COMMAND_FIELD)
                );
            }

            if (
                array_key_exists(DataDefinitionField::ARGUMENTS_FIELD, $decodedDataDefinitionField) &&
                !is_array($decodedDataDefinitionField[DataDefinitionField::ARGUMENTS_FIELD])
            ) {
                throw new InvalidDataDefinitionFieldException(
                    sprintf('Data definition field "%s" must be array.', DataDefinitionField::ARGUMENTS_FIELD)
                );
            }

            return;
        }

        throw new InvalidDataDefinitionFieldException(
            sprintf(
                'Data definition field must contain either "%s" or "%s" field.',
                DataDefinitionField::VALUE_FIELD,
                DataDefinitionField::COMMAND_FIELD
            )
        );
    }
}
