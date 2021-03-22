<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver;

interface SchemaFieldValueResolverInterface
{
    /**
     * @param array<string, mixed> $decodedSchema
     * @param array<integer, string> $path
     * @param bool $isRootSchema
     * @return mixed
     */
    public function getValue(array $decodedSchema, array $path, bool $isRootSchema = false);
}
