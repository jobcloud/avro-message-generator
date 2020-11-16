<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

/**
 * Interface PayloadGeneratorInterface
 */
interface PayloadGeneratorInterface
{
    /**
     * @param string|array<string, mixed> $decodedSchema
     * @return mixed
     * @throws UnsupportedAvroSchemaTypeException
     */
    public function generate($decodedSchema, SchemaFieldValueResolverInterface $schemaFieldValueResolver);
}
