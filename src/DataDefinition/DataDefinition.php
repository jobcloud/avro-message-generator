<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;

/**
 * Class DataDefinition
 */
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
     * @return array<string|integer, DataDefinitionFieldInterface>
     */
    public function getDataDefinitionFields(): array
    {
        return $this->dataDefinitionFields;
    }

    /**
     * @param string|integer $dataDefinitionFieldKey
     * @return DataDefinitionFieldInterface|null
     */
    public function getDataDefinitionField($dataDefinitionFieldKey = 0): ?DataDefinitionFieldInterface
    {
        if (!isset($this->dataDefinitionFields[$dataDefinitionFieldKey])) {
            return null;
        }

        return $this->dataDefinitionFields[$dataDefinitionFieldKey];
    }
}
