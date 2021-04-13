<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory;

use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;

interface SchemaFieldValueResolverFactoryInterface
{
    /**
     * @param string $topicName
     * @param mixed $predefinedPayload
     * @return SchemaFieldValueResolverInterface
     * @throws UnexistingDataDefinitionException
     */
    public function create(string $topicName, $predefinedPayload): SchemaFieldValueResolverInterface;
}
