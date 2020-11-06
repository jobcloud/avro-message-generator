<?php


namespace Jobcloud\Avro\Message\Generator\DataDefinition\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;

/**
 * Interface DataDefinitionFactoryInterface
 */
interface DataDefinitionFactoryInterface
{
    /**
     * @param array<string|integer, mixed> $decodedDataDefinition
     * @return DataDefinition
     * @throws InvalidDataDefinitionFieldException
     */
    public function create(array $decodedDataDefinition): DataDefinition;
}
