<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload\Factory;

use Jobcloud\Avro\Message\Generator\Payload\PayloadGeneratorInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

interface PayloadGeneratorFactoryInterface
{
    /**
     * @param SchemaFieldValueResolverInterface $schemaFieldValueResolver
     * @return PayloadGeneratorInterface
     */
    public function create(SchemaFieldValueResolverInterface $schemaFieldValueResolver): PayloadGeneratorInterface;
}
