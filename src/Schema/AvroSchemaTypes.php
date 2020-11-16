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
    public const STRING_TYPE = "string";

    /** @var string */
    public const RECORD_TYPE = "record";

    /** @var string */
    public const ENUM_TYPE = "enum";

    /** @var string */
    public const ARRAY_TYPE = "array";

    /** @var string */
    public const MAP_TYPE = "map";

    /**
     * @return array<integer, string>
     */
    public static function getSupportedSchemaTypes(): array
    {
        return [
            self::NULL_TYPE,
            self::BOOLEAN_TYPE,
            self::INT_TYPE,
            self::LONG_TYPE,
            self::FLOAT_TYPE,
            self::DOUBLE_TYPE,
            self::STRING_TYPE,
            self::RECORD_TYPE,
            self::ENUM_TYPE,
            self::ARRAY_TYPE,
            self::MAP_TYPE
        ];
    }

    /**
     * @return array<integer, string>
     */
    public static function getSimpleSchemaTypes(): array
    {
        return [
            self::NULL_TYPE,
            self::BOOLEAN_TYPE,
            self::INT_TYPE,
            self::LONG_TYPE,
            self::FLOAT_TYPE,
            self::DOUBLE_TYPE,
            self::STRING_TYPE,
            self::ENUM_TYPE
        ];
    }
}
