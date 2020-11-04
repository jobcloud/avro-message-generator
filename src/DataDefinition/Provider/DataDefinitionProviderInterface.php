<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Provider;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJson;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;

/**
 * Interface DataDefinitionProviderInterface
 */
interface DataDefinitionProviderInterface
{
    /**
     * @return void
     * @throws IncorrectDataDefinitionJson
     */
    public function load(): void;

    /**
     * @param string $dataDefinitionName
     * @return DataDefinition
     * @throws UnexistingDataDefinitionException
     */
    public function getDataDefinition(string $dataDefinitionName): DataDefinition;
}
