<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\Schema\Field\Value\Resolver;

use Faker\Factory;
use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionFieldInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolver
 */
class SchemaFieldValueResolverTest extends TestCase
{
    public function testRootSchemaWithPredefinedPayload(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            'testPredefinedPayload'
        );

        $value = $schemaFieldValueResolver->getValue([
            'name' => 'testName'
        ], [], true);

        self::assertSame('testPredefinedPayload', $value);
    }

    public function testRootSchemaWithExistingDataDefinition(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(true);

        $dataDefinition->expects(self::once())->method('getDataDefinitionField')->with(0)
            ->willReturn($dataDefinitionField);

        $dataDefinitionField->expects(self::once())->method('isCommandField')->willReturn(false);
        $dataDefinitionField->expects(self::never())->method('getCommand');
        $dataDefinitionField->expects(self::once())->method('getValue')->willReturn('testValue');


        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string'
        ], [], true);

        self::assertSame('testValue', $value);
    }

    public function testRootSchemaWithExistingDataDefinitionWithCommand(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(true);

        $dataDefinition->expects(self::once())->method('getDataDefinitionField')->with(0)
            ->willReturn($dataDefinitionField);

        $dataDefinitionField->expects(self::once())->method('isCommandField')->willReturn(true);
        $dataDefinitionField->expects(self::once())->method('getCommand')->willReturn('shuffle');
        $dataDefinitionField->expects(self::once())->method('getArguments')->willReturn([[1]]);
        $dataDefinitionField->expects(self::never())->method('getValue');


        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'command' => 'shuffle',
            'arguments' => [[1]]
        ], [], true);

        self::assertSame(null, $value);
    }

    public function testRootSchemaWithExistingDataDefinitionWithCommandAndWithoutArguments(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(true);

        $dataDefinition->expects(self::once())->method('getDataDefinitionField')->with(0)
            ->willReturn($dataDefinitionField);

        $dataDefinitionField->expects(self::once())->method('isCommandField')->willReturn(true);
        $dataDefinitionField->expects(self::once())->method('getCommand')->willReturn('shuffle');
        $dataDefinitionField->expects(self::once())->method('getArguments')->willReturn([]);
        $dataDefinitionField->expects(self::never())->method('getValue');


        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'command' => 'shuffle'
        ], [], true);

        self::assertSame(null, $value);
    }

    public function testRootSchemaWithExistingGlobalDataDefinition(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(true);

        $globalDataDefinition->expects(self::once())->method('getDataDefinitionField')->with(0)
            ->willReturn($dataDefinitionField);

        $dataDefinitionField->expects(self::once())->method('isCommandField')->willReturn(false);
        $dataDefinitionField->expects(self::never())->method('getCommand');
        $dataDefinitionField->expects(self::once())->method('getValue')->willReturn('testValue');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string'
        ], [], true);

        self::assertSame('testValue', $value);
    }

    public function testStringRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string'
        ], [], true);

        self::assertIsString($value);
    }

    public function testNullRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'null'
        ], [], true);

        self::assertNull($value);
    }

    public function testBooleanRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
            ->addMethods(['title'])
            ->getMock();

        $faker->expects(self::once())->method('title')->with()->willReturn('Mr.');

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'boolean'
        ], [], true);

        self::assertTrue($value);
    }

    public function testIntRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'int'
        ], [], true);

        self::assertIsInt($value);
    }

    public function testLongRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'long'
        ], [], true);

        self::assertIsInt($value);
    }

    public function testFloatRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['randomFloat'])
            ->getMock();

        $faker->expects(self::once())->method('randomFloat')->with(1)->willReturn(2.12);

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'float'
        ], [], true);

        self::assertIsFloat($value);
        self::assertSame(2.12, $value);
    }

    public function testDoubleRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['randomFloat'])
            ->getMock();

        $faker->expects(self::once())->method('randomFloat')->with(1)->willReturn(2.12);

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'double'
        ], [], true);

        self::assertIsFloat($value);
        self::assertSame(2.12, $value);
    }

    public function testEnumRootSchemaWithoutDataDefinitions(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with(0)
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            null
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'enum',
            'symbols' => ['TEST']
        ], [], true);

        self::assertSame('TEST', $value);
    }

    public function testNestedSchemaWithPredefinedPayloadAndWithEmptyPath(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            ['testNameKey' => 'testNameValue']
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string',
            'name' => 'testNameKey'
        ], [], false);

        self::assertSame('testNameValue', $value);
    }

    public function testNestedSchemaWithInvalidPredefinedPayload(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
            ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with('pathKey.testNameKey')
            ->willReturn(false);

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::once())->method('hasDataDefinitionField')->with('testNameKey')
            ->willReturn(false);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            ['pathKey' => 1]
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string',
            'name' => 'testNameKey'
        ], ['pathKey'], false);

        self::assertIsString($value);
    }

    public function testNestedSchemaWithPredefinedPayloadAndValidPath(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $dataDefinition->expects(self::never())->method('getDataDefinitionField');

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            ['pathKey' => [
                'nestedPathKey' => [
                    'wrongNameKey' => 'testWrongValue',
                    'testNameKey' => 'testVALUE'
                ]
            ]]
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string',
            'name' => 'testNameKey'
        ], ['pathKey', 'nestedPathKey'], false);

        self::assertSame('testVALUE', $value);
    }

    public function testNestedSchemaWithDataDefinition(): void
    {
        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $dataDefinition */
        $dataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasDataDefinitionField', 'getDataDefinitionField', 'getDataDefinitionFields'])
        ->getMock();

        /** @var DataDefinitionInterface|MockObject $globalDataDefinition */
        $globalDataDefinition = $this->getMockBuilder(DataDefinitionInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var DataDefinitionFieldInterface|MockObject $dataDefinitionField */
        $dataDefinitionField = $this->getMockBuilder(DataDefinitionFieldInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataDefinitionField->expects(self::once())->method('isCommandField')->willReturn(false);
        $dataDefinitionField->expects(self::never())->method('getCommand');
        $dataDefinitionField->expects(self::once())->method('getValue')->willReturn('testValue');

        $dataDefinition->expects(self::once())->method('hasDataDefinitionField')->with('pathKey.testNameKey')
            ->willReturn(true);

        $dataDefinition->expects(self::once())->method('getDataDefinitionField')->with('pathKey.testNameKey')
            ->willReturn($dataDefinitionField);

        $globalDataDefinition->expects(self::never())->method('getDataDefinitionField');

        $schemaFieldValueResolver = new SchemaFieldValueResolver(
            $faker,
            $dataDefinition,
            $globalDataDefinition,
            ['pathKey' => 1]
        );

        $value = $schemaFieldValueResolver->getValue([
            'type' => 'string',
            'name' => 'testNameKey.'
        ], ['pathKey'], false);

        self::assertSame('testValue', $value);
    }
}
