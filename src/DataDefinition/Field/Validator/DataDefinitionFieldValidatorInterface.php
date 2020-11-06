<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator;

use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;

/**
 * Interface DataDefinitionFieldValidatorInterface
 */
interface DataDefinitionFieldValidatorInterface
{
    /**
     * @param array<string|integer, mixed> $decodedDataDefinitionField
     * @return void
     * @throws InvalidDataDefinitionFieldException
     */
    public function validateDataDefinitionField(array $decodedDataDefinitionField): void;
}
