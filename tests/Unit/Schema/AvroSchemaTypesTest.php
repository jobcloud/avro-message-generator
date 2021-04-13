<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\Schema;

use Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Schema\AvroSchemaTypes
 */
class AvroSchemaTypesTest extends TestCase
{
    public function testGetSupportedSchemaTypes(): void
    {
        self::assertEquals([
            "null",
            "boolean",
            "int",
            "long",
            "float",
            "double",
            "string",
            "record",
            "enum",
            "array",
            "map"
        ], AvroSchemaTypes::getSupportedSchemaTypes());
    }

    public function testGetSimpleSchemaTypes(): void
    {
        self::assertEquals([
            "null",
            "boolean",
            "int",
            "long",
            "float",
            "double",
            "string",
            "enum"
        ], AvroSchemaTypes::getSimpleSchemaTypes());
    }
}
