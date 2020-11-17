<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Provider;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJsonException;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;

/**
 * Interface DataDefinitionProviderInterface
 */
interface DataDefinitionProviderInterface
{
    /**
     * @return void
     * @throws IncorrectDataDefinitionJsonException
     */
    public function load(): void;

    /**
     * @param string $dataDefinitionName
     * @return DataDefinitionInterface
     * @throws UnexistingDataDefinitionException
     */
    public function getDataDefinition(string $dataDefinitionName): DataDefinitionInterface;
}
