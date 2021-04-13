<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactoryInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidatorInterface;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;

class DataDefinitionFactory implements DataDefinitionFactoryInterface
{
    private DataDefinitionFieldValidatorInterface $dataDefinitionFieldValidator;

    private DataDefinitionFieldFactoryInterface $dataDefinitionFieldFactory;

    public function __construct(
        DataDefinitionFieldValidatorInterface $dataDefinitionFieldValidator,
        DataDefinitionFieldFactoryInterface $dataDefinitionFieldFactory
    ) {
        $this->dataDefinitionFieldValidator = $dataDefinitionFieldValidator;
        $this->dataDefinitionFieldFactory = $dataDefinitionFieldFactory;
    }

    /**
     * @param array<string|integer, mixed> $decodedDataDefinition
     * @return DataDefinitionInterface
     * @throws InvalidDataDefinitionFieldException
     */
    public function create(array $decodedDataDefinition): DataDefinitionInterface
    {
        try {
            $this->dataDefinitionFieldValidator->validateDataDefinitionField($decodedDataDefinition);

            $dataDefinitionField = $this->dataDefinitionFieldFactory->create($decodedDataDefinition);

            $dataDefinitionFields = [$dataDefinitionField];

            return new DataDefinition($dataDefinitionFields);
        } catch (InvalidDataDefinitionFieldException $e) {
            $dataDefinitionFields = [];

            foreach ($decodedDataDefinition as $dataDefinitionFieldKey => $dataDefinitionField) {
                $this->dataDefinitionFieldValidator->validateDataDefinitionField($dataDefinitionField);

                $dataDefinitionFields[$dataDefinitionFieldKey] = $this->dataDefinitionFieldFactory->create(
                    $dataDefinitionField
                );
            }

            return new DataDefinition($dataDefinitionFields);
        }
    }
}
