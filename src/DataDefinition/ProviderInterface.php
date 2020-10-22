<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Exception;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * @param string $dataDefinitionName
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getDataDefinition(string $dataDefinitionName): array;
}
