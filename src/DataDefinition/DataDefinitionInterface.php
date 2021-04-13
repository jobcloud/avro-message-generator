<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

interface DataDefinitionInterface
{
    /**
     * @param mixed $dataDefinitionFieldKey
     * @return bool
     */
    public function hasDataDefinitionField($dataDefinitionFieldKey): bool;

    /**
     * @return array<string|integer, DataDefinitionFieldInterface>
     */
    public function getDataDefinitionFields(): array;

    /**
     * @param mixed $dataDefinitionFieldKey
     * @return DataDefinitionFieldInterface|null
     */
    public function getDataDefinitionField($dataDefinitionFieldKey): ?DataDefinitionFieldInterface;
}
