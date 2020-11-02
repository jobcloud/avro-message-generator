<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

/**
 * Class GlobalDataDefinitionApplier
 */
class GlobalDataDefinitionApplier implements GlobalDataDefinitionApplierInterface
{
    /**
     * @param array<string|integer, mixed> $dataDefinition
     * @param array<string|integer, mixed> $globalDataDefinition
     * @return array<string|integer, mixed>
     */
    public function applyGlobalDataDefinition(array $dataDefinition, array $globalDataDefinition): array
    {
        if ([] === $dataDefinition || [] === $globalDataDefinition) {
            return $dataDefinition;
        }

        $mappedGlobalDataDefinition = [];

        foreach ($globalDataDefinition as $field) {
            if (!isset($field['name'])) {
                continue;
            }

            $mappedGlobalDataDefinition[$field['name']] = $field;
        }

        if ([] === $mappedGlobalDataDefinition) {
            return $dataDefinition;
        }

        foreach ($dataDefinition as $key => $field) {
            if (isset($field['name']) && isset($mappedGlobalDataDefinition[$field['name']])) {
                $dataDefinition[$key] = $mappedGlobalDataDefinition[$field['name']];

                continue;
            }

            if (isset($field['definitions']) && is_array($field['definitions'])) {
                $dataDefinition[$key]['definitions'] = $this->applyGlobalDataDefinition(
                    $field['definitions'],
                    $globalDataDefinition
                );
            }
        }

        return $dataDefinition;
    }
}
