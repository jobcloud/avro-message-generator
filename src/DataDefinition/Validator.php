<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionStructure;

/**
 * Class Validator
 */
class Validator implements ValidatorInterface
{
    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateSimpleSchemaTypeDataDefinition(array $dataDefinition): void
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
            if (!array_key_exists('value', $dataDefinition)) {
                throw new InvalidDataDefinitionStructure(
                    'Item of type "value" must have "value" field.'
                );
            }

            return;
        }

        if (!isset($dataDefinition['command'])) {
            throw new InvalidDataDefinitionStructure(
                'Item of type "faker" must have "command" field.'
            );
        }
    }

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateComplexSchemaTypeDataDefinition(array $dataDefinition): void
    {
        if (!isset($dataDefinition['definitions']) || !is_array($dataDefinition['definitions'])) {
            throw new InvalidDataDefinitionStructure(
                'Data definition item which refers to complex schema type must contain "definitions" field.'
            );
        }
    }

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param string $schemaType
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateDataDefinitionNameField(array $dataDefinition, string $schemaType): void
    {
        if (!isset($dataDefinition['name']) || trim($dataDefinition['name']) === '') {
            throw new InvalidDataDefinitionStructure(
                sprintf(
                    'Data definition item which refers to the item of "%s" schema type must contain "name" field.',
                    $schemaType
                )
            );
        }
    }

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param string $name
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateDataDefinitionByName(array $dataDefinition, string $name): void
    {
        if (!isset($dataDefinition[$name])) {
            throw new InvalidDataDefinitionStructure(
                sprintf('Data definition for field "%s" is missing.', $name)
            );
        }
    }
}
