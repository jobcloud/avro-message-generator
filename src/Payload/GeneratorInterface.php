<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;

/**
 * Interface GeneratorInterface
 */
interface GeneratorInterface
{
    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<string, mixed> $dataDefinition
     * @param mixed $predefinedPayload
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    public function generate(array $decodedSchema, array $dataDefinition, $predefinedPayload);
}
