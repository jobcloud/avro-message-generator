<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

/**
 * Interface GlobalDataDefinitionApplierInterface
 */
interface GlobalDataDefinitionApplierInterface
{
    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param array<string|integer, mixed> $globalDataDefinition
     * @return array<string|integer, mixed>
     */
    public function applyGlobalDataDefinition(array $dataDefinition, array $globalDataDefinition): array;
}
