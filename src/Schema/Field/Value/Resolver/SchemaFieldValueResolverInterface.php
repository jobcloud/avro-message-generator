<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;

/**
 * Interface SchemaFieldValueResolverInterface
 */
interface SchemaFieldValueResolverInterface
{
    /**
     * @param string $schemaType
     * @param string|null $fieldName
     * @param array<integer, string> $path
     * @return mixed
     * @throws MissingCommandExecutorException
     */
    public function getValue(string $schemaType, ?string $fieldName, array $path);
}