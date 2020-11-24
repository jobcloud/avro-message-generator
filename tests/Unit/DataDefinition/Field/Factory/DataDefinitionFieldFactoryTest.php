<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Field\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactory
 */
class DataDefinitionFieldFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $dataDefinitionFieldFactory = new DataDefinitionFieldFactory();

        $dataDefinitionField = $dataDefinitionFieldFactory->create(["value" => "test"]);

        self::assertInstanceOf(DataDefinitionFieldInterface::class, $dataDefinitionField);
    }
}
