<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema;

/**
 * Class AvroSchemaTypes
 */
class AvroSchemaTypes
{
    /** @var string */
    public const NULL_TYPE = "null";

    /** @var string */
    public const BOOLEAN_TYPE = "boolean";

    /** @var string */
    public const INT_TYPE = "int";

    /** @var string */
    public const LONG_TYPE = "long";

    /** @var string */
    public const FLOAT_TYPE = "float";

    /** @var string */
    public const DOUBLE_TYPE = "double";

    /** @var string */
    public const BYTES_TYPE = "bytes";

    /** @var string */
    public const STRING_TYPE = "string";

    /** @var string */
    public const RECORD_TYPE = "record";

    /** @var string */
    public const ENUM_TYPE = "enum";

    /** @var string */
    public const ARRAY_TYPE = "array";

    /** @var string */
    public const MAP_TYPE = "map";

    /** @var string */
    public const FIXED_TYPE = "fixed";
}
