<?php


namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition
 */
class DataDefinitionTest extends TestCase
{
    public function testHasDataDefinitionField(): void
    {
        $dataDefinition = new DataDefinition([
            'testkey' => new DataDefinitionField([])
        ]);

        self::assertTrue($dataDefinition->hasDataDefinitionField('testkey'));
        self::assertFalse($dataDefinition->hasDataDefinitionField('testkey1'));
        self::assertFalse($dataDefinition->hasDataDefinitionField(null));
    }

    public function testGetDataDefinitionFields(): void
    {
        $dataDefinition = new DataDefinition([
            'testkey' => new DataDefinitionField([])
        ]);

        $fields = $dataDefinition->getDataDefinitionFields();

        self::assertIsArray($fields);
        self::assertCount(1, $fields);
    }

    public function testGetDataDefinitionField(): void
    {
        $dataDefinition = new DataDefinition([
            'testkey' => new DataDefinitionField([])
        ]);

        $dataDefinitionField = $dataDefinition->getDataDefinitionField('testkey');

        self::assertInstanceOf(DataDefinitionFieldInterface::class, $dataDefinitionField);
    }
}
