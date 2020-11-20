<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;
use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;

/**
 * Interface PayloadGeneratorInterface
 */
interface PayloadGeneratorInterface
{
    /**
     * @param string|array<string, mixed> $decodedSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException|MissingCommandExecutorException
     */
    public function generate($decodedSchema);
}
