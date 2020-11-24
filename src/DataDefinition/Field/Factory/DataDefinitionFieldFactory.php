<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

/**
 * Class DataDefinitionFieldFactory
 */
class DataDefinitionFieldFactory implements DataDefinitionFieldFactoryInterface
{
    /**
     * @param array<string, mixed> $decodedDataDefinitionField
     * @return DataDefinitionFieldInterface
     */
    public function create(array $decodedDataDefinitionField): DataDefinitionFieldInterface
    {
        return new DataDefinitionField($decodedDataDefinitionField);
    }
}
