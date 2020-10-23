<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJson;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionFile;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * @param string $dataDefinitionName
     * @return array<string, mixed>
     * @throws UnexistingDataDefinitionFile|IncorrectDataDefinitionJson
     */
    public function getDataDefinition(string $dataDefinitionName): array;
}
