<?php

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;

interface DataDefinitionFactoryInterface
{
    /**
     * @param array<string|integer, mixed> $decodedDataDefinition
     * @return DataDefinitionInterface
     * @throws InvalidDataDefinitionFieldException
     */
    public function create(array $decodedDataDefinition): DataDefinitionInterface;
}
