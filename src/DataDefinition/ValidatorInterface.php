<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionStructure;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateSimpleSchemaTypeDataDefinition(array $dataDefinition): void;

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateComplexSchemaTypeDataDefinition(array $dataDefinition): void;

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param string $schemaType
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateDataDefinitionNameField(array $dataDefinition, string $schemaType): void;

    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param string $name
     * @return void
     * @throws InvalidDataDefinitionStructure
     */
    public function validateDataDefinitionByName(array $dataDefinition, string $name): void;
}
