<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field;

use Jobcloud\Avro\Message\Generator\Exception\MissingCommandExecutorException;

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
     * @param object|null $executor
     * @return mixed
     * @throws MissingCommandExecutorException
     */
    public function getValue(?object $executor)
    {
        if (null !== $this->command) {
            if (null === $executor) {
                throw new MissingCommandExecutorException(
                    sprintf('Missing executor for "%s" command.', $this->command)
                );
            }

            if (null === $this->arguments) {
                $this->arguments = [];
            }
            /** @phpstan-ignore-next-line */
            return call_user_func_array(array($executor, $this->command), $this->arguments);
        }

        return $this->value;
    }
}
