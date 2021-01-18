<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Field;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField
 */
class DataDefinitionFieldTest extends TestCase
{
    public function testGetValueWithoutCommand(): void
    {
        $dataDefinitionField = new DataDefinitionField(['value' => 'test']);

        self::assertSame('test', $dataDefinitionField->getValue());
    }

    public function testGetCommandWithoutArguments(): void
    {
        $dataDefinitionField = new DataDefinitionField(['command' => 'word']);

        self::assertSame('word', $dataDefinitionField->getCommand());
    }

    public function testGetCommandWitArguments(): void
    {
        $dataDefinitionField = new DataDefinitionField(['command' => 'randomDigitNot', 'arguments' => [5]]);

        self::assertSame('randomDigitNot', $dataDefinitionField->getCommand());
        self::assertSame([5], $dataDefinitionField->getArguments());
    }
}
