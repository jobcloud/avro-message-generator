<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactoryInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidatorInterface;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactory
 */
class DataDefinitionFactoryTest extends TestCase
{
    public function testCreateSimpleSchema(): void
    {
        /** @var DataDefinitionFieldValidatorInterface|MockObject $dataDefinitionFieldValidator */
        $dataDefinitionFieldValidator = $this->getMockBuilder(DataDefinitionFieldValidatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateDataDefinitionField'])
        ->getMock();

        /** @var DataDefinitionFieldFactoryInterface|MockObject $dataDefinitionFieldFactory */
        $dataDefinitionFieldFactory = $this->getMockBuilder(DataDefinitionFieldFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $dataDefinitionFieldValidator->expects(self::once())->method('validateDataDefinitionField')
            ->with(["value" => "testValue"]);

        $dataDefinitionFieldFactory->expects(self::once())->method('create')
            ->with(["value" => "testValue"])
            ->willReturn($dataDefinitionField);

        $dataDefinitionFactory = new DataDefinitionFactory(
            $dataDefinitionFieldValidator,
            $dataDefinitionFieldFactory
        );

        $dataDefinition = $dataDefinitionFactory->create(["value" => "testValue"]);

        self::assertInstanceOf(DataDefinitionInterface::class, $dataDefinition);
    }

    public function testCreateNeatedSchema(): void
    {
        /** @var DataDefinitionFieldValidatorInterface|MockObject $dataDefinitionFieldValidator */
        $dataDefinitionFieldValidator = $this->getMockBuilder(DataDefinitionFieldValidatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateDataDefinitionField'])
            ->getMock();

        /** @var DataDefinitionFieldFactoryInterface|MockObject $dataDefinitionFieldFactory */
        $dataDefinitionFieldFactory = $this->getMockBuilder(DataDefinitionFieldFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new InvalidDataDefinitionFieldException('error message');

        $dataDefinitionFieldValidator->expects(self::exactly(2))->method('validateDataDefinitionField')
            ->withConsecutive([["testKey" => ["value" => "testValue"]]], [["value" => "testValue"]])
            ->willReturnOnConsecutiveCalls($this->throwException($exception), null);

        $dataDefinitionFieldFactory->expects(self::once())->method('create')
            ->with(["value" => "testValue"])
            ->willReturn($dataDefinitionField);

        $dataDefinitionFactory = new DataDefinitionFactory(
            $dataDefinitionFieldValidator,
            $dataDefinitionFieldFactory
        );

        $dataDefinition = $dataDefinitionFactory->create(["testKey" => ["value" => "testValue"]]);

        self::assertInstanceOf(DataDefinitionInterface::class, $dataDefinition);
    }
}
