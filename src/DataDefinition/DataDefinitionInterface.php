<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

/**
 * Interface DataDefinitionInterface
 */
interface DataDefinitionInterface
{
    /**
     * @return array<string|integer, DataDefinitionFieldInterface>
     */
    public function getDataDefinitionFields(): array;

    /**
     * @param string|integer $dataDefinitionFieldKey
     * @return DataDefinitionFieldInterface|null
     */
    public function getDataDefinitionField($dataDefinitionFieldKey = 0): ?DataDefinitionFieldInterface;
}
