<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Field\Validator;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidator
 */
class DataDefinitionFieldValidatorTest extends TestCase
{
    public function testValidateWithInvalidFields(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::expectExceptionMessage('Data definition field can contain following fields: value, command, arguments.');

        $validator->validateDataDefinitionField(['test' => 'test']);
    }

    public function testValidateWithValueFieldAndAdditionalField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::expectExceptionMessage('Data definition field of type "value" can not contain other fields.');

        $validator->validateDataDefinitionField(['value' => 'test', 'command' => 'test']);
    }

    public function testValidateWithValueField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::assertNull($validator->validateDataDefinitionField(['value' => 'test']));
    }

    public function testValidateWithEmptyCommandField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::expectExceptionMessage('Data definition field "command" must be string.');

        $validator->validateDataDefinitionField(['command' => ' ']);
    }

    public function testValidateWithInvalidArgumentsField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::expectExceptionMessage('Data definition field "arguments" must be array.');

        $validator->validateDataDefinitionField(['command' => 'test', 'arguments' => 'test']);
    }

    public function testValidateWithValidCommandAndValidArgumentsField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::assertNull($validator->validateDataDefinitionField(['command' => 'test', 'arguments' => ['test']]));
    }

    public function testValidateWithOnlyArgumentsField(): void
    {
        $validator = new DataDefinitionFieldValidator();

        self::expectExceptionMessage('Data definition field must contain either "value" or "command" field.');

        $validator->validateDataDefinitionField(['arguments' => ['test']]);
    }
}
