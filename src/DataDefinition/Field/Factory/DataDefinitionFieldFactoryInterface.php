<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

/**
 * Interface DataDefinitionFieldFactoryInterface
 */
interface DataDefinitionFieldFactoryInterface
{
    /**
     * @param array<string|integer, mixed> $decodedDataDefinitionField
     * @return DataDefinitionFieldInterface
     */
    public function create(array $decodedDataDefinitionField): DataDefinitionFieldInterface;
}
