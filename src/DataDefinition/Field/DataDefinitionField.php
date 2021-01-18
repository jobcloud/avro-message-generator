<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field;

/**
 * Class DataDefinitionField
 */
class DataDefinitionField implements DataDefinitionFieldInterface
{
    /** @var string */
    public const VALUE_FIELD = 'value';

    /** @var string */
    public const COMMAND_FIELD = 'command';

    /** @var string */
    public const ARGUMENTS_FIELD = 'arguments';

    /** @var string */
    public const PATH_DELIMITER = '.';

    /** @var mixed */
    private $value = null;

    private ?string $command = null;

    /** @var array<integer, mixed>|null */
    private ?array $arguments = null;

    /**
     * @param array<string|integer, mixed> $decodedDataDefinitionField
     */
    public function __construct(array $decodedDataDefinitionField)
    {
        if (array_key_exists(self::VALUE_FIELD, $decodedDataDefinitionField)) {
            $this->value = $decodedDataDefinitionField[self::VALUE_FIELD];
        }

        if (array_key_exists(self::COMMAND_FIELD, $decodedDataDefinitionField)) {
            $this->command = $decodedDataDefinitionField[self::COMMAND_FIELD];
        }

        if (array_key_exists(self::ARGUMENTS_FIELD, $decodedDataDefinitionField)) {
            $this->arguments = $decodedDataDefinitionField[self::ARGUMENTS_FIELD];
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * @return array<integer, mixed>|null
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }
}
