<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field;

use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;

/**
 * Interface DataDefinitionFieldInterface
 */
interface DataDefinitionFieldInterface
{
    /**
     * @param object|null $executor
     * @return mixed
     * @throws MissingCommandExecutorException
     */
    public function getValue(?object $executor);
}
