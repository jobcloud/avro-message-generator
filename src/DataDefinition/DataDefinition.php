<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

class DataDefinition implements DataDefinitionInterface
{
    /** @var array<string|integer, DataDefinitionFieldInterface> */
    private array $dataDefinitionFields;

    /**
     * @param array<string|integer, DataDefinitionFieldInterface> $dataDefinitionFields
     */
    public function __construct(array $dataDefinitionFields)
    {
        $this->dataDefinitionFields = $dataDefinitionFields;
    }

    /**
     * @param mixed $dataDefinitionFieldKey
     * @return bool
     */
    public function hasDataDefinitionField($dataDefinitionFieldKey): bool
    {
        if (false === $this->isValidDataDefinitionFieldKey($dataDefinitionFieldKey)) {
            return false;
        }

        return array_key_exists($dataDefinitionFieldKey, $this->dataDefinitionFields);
    }

    /**
     * @return array<string|integer, DataDefinitionFieldInterface>
     */
    public function getDataDefinitionFields(): array
    {
        return $this->dataDefinitionFields;
    }

    /**
     * @param mixed $dataDefinitionFieldKey
     * @return DataDefinitionFieldInterface|null
     */
    public function getDataDefinitionField($dataDefinitionFieldKey): ?DataDefinitionFieldInterface
    {
        return $this->dataDefinitionFields[$dataDefinitionFieldKey] ?? null;
    }

    /**
     * @param mixed $dataDefinitionFieldKey
     * @return bool
     */
    private function isValidDataDefinitionFieldKey($dataDefinitionFieldKey): bool
    {
        if (is_string($dataDefinitionFieldKey)) {
            return true;
        }

        if (is_integer($dataDefinitionFieldKey)) {
            return true;
        }

        return false;
    }
}
