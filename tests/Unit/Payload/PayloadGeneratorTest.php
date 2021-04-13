<?php


namespace Jobcloud\Avro\Message\Generator\Tests\Unit\Payload;

use Jobcloud\Avro\Message\Generator\Exception\UnsupportedAvroSchemaTypeException;
use Jobcloud\Avro\Message\Generator\Payload\PayloadGenerator;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Payload\PayloadGenerator
 */
class PayloadGeneratorTest extends TestCase
{
    public function testGenerateNotSupportedSchemaTypeWithSimpleSchemaStructure(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        $schemaFieldValueResolver->expects(self::never())->method('getValue');

        self::expectException(UnsupportedAvroSchemaTypeException::class);
        self::expectExceptionMessage('Schema type "test" is not supported.');

        $payloadGenerator->generate('test');
    }

    public function testGenerateNotSupportedSchemaTypeWithComplexSchemaStructure(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        $schemaFieldValueResolver->expects(self::never())->method('getValue');

        self::expectExceptionMessage('Schema type "test" is not supported.');

        $payloadGenerator->generate(['type' => 'test']);
    }

    public function testGenerateWithoutTypeField(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        $schemaFieldValueResolver->expects(self::never())->method('getValue');

        self::expectExceptionMessage('Schema must contain type attribute.');

        $payloadGenerator->generate(['test' => 'test']);
    }

    public function testGenerateForSimpleSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
        ->getMock();

        $schemaFieldValueResolver->expects(self::once())->method('getValue')->with(
            ['type' => 'string'],
            [],
            true
        )->willReturn('some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertIsString($payloadGenerator->generate(['type' => 'string']));
    }

    public function testGenerateForRecordSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::exactly(2))->method('getValue')
            ->withConsecutive([
                [
                    'name' => 'testField1',
                    'type' => 'string'
                ],
                [],
                false
            ], [
                [
                    'name' => 'testField2',
                    'type' => 'string'
                ],
                [],
                false
            ])->willReturnOnConsecutiveCalls('some string 1', 'some string 2');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            'testField1' => 'some string 1',
            'testField2' => 'some string 2'
        ], $payloadGenerator->generate([
            'type' => 'record',
            'name' => 'test',
            'fields' => [
                [
                    'name' => 'testField1',
                    'type' => 'string'
                ],
                [
                    'name' => 'testField2',
                    'type' => 'string'
                ]
            ]
        ]));
    }

    public function testGenerateForNestedRecordSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
        ->getMock();

        $schemaFieldValueResolver->expects(self::exactly(2))->method('getValue')
            ->withConsecutive([
                [
                    'name' => 'nestedRecordSchemaField',
                    'type' => 'string'
                ],
                ['testField1', 'nestedRecordSchema'],
                false
            ], [
                [
                    'name' => 'testField2',
                    'type' => 'string'
                ],
                [],
                false
            ])->willReturnOnConsecutiveCalls('some string 1', 'some string 2');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            'testField1' => [
               'nestedRecordSchemaField' => 'some string 1'
            ],
            'testField2' => 'some string 2'
        ], $payloadGenerator->generate([
            'type' => 'record',
            'name' => 'test',
            'fields' => [
                [
                    'name' => 'testField1',
                    'type' => [
                        'type' => 'record',
                        'name' => 'nestedRecordSchema',
                        'fields' => [
                            [
                                'name' => 'nestedRecordSchemaField',
                                'type' => 'string'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'testField2',
                    'type' => 'string'
                ]
            ]
        ]));
    }

    public function testGenerateForArraySchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
        ->getMock();

        $schemaFieldValueResolver->expects(self::once())->method('getValue')
            ->with(['type' => 'string'], [], false)
        ->willReturn('some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            'some string'
        ], $payloadGenerator->generate([
            'type' => 'array',
            'items' => 'string'
        ]));
    }

    public function testGenerateForComplexArraySchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::once())->method('getValue')
            ->with([
                'name' => 'testField',
                'type' => 'string'
            ], ['recordItem'], false)
            ->willReturn('some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            ['testField' => 'some string']
        ], $payloadGenerator->generate([
            'type' => 'array',
            'items' => [
                'type' => 'record',
                'name' => 'recordItem',
                'fields' => [
                    [
                        'name' => 'testField',
                        'type' => 'string'
                    ]
                ]
            ]
        ]));
    }

    public function testGenerateForComplexArraySchemaWithZeroIndex(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::once())->method('getValue')
            ->with([
                'name' => 'testField',
                'type' => 'string'
            ], [0], false)
            ->willReturn('some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            ['testField' => 'some string']
        ], $payloadGenerator->generate([
            'type' => 'array',
            'items' => [
                'type' => 'record',
                'fields' => [
                    [
                        'name' => 'testField',
                        'type' => 'string'
                    ]
                ]
            ]
        ]));
    }

    public function testGenerateForNotSupportedSchemaWithComplexStructure(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::never())->method('getValue');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::expectException(UnsupportedAvroSchemaTypeException::class);

        $payloadGenerator->generate([
            'type' => 'array',
            'items' => [
                'type' => [null, 'string']
            ]
        ]);
    }

    public function testGenerateForUnionSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::exactly(2))->method('getValue')
            ->withConsecutive([
                [
                    'name' => 'nestedField',
                    'type' => 'null'
                ],
                [],
                false
            ], [
                [
                    'name' => 'nestedField',
                    'type' => 'string'
                ],
                [],
                false
            ])
            ->willReturnOnConsecutiveCalls(null, 'some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        $payloadGenerator->generate([
            'type' => 'record',
            'name' => 'record',
            'fields' => [
                [
                    'name' => 'nestedField',
                    'type' => ["null", 'string']
                ]
            ]
        ]);
    }

    public function testGenerateForSimpleUnionSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::once())->method('getValue')
            ->with(
                [
                    'name' => 'nestedField',
                    'type' => 'string'
                ],
                [],
                false)
            ->willReturn('some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame(['nestedField' => 'some string'], $payloadGenerator->generate([
            'type' => 'record',
            'name' => 'record',
            'fields' => [
                [
                    'name' => 'nestedField',
                    'type' => ['string']
                ]
            ]
        ]));
    }

    public function testGenerateForMapSchema(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $schemaFieldValueResolver->expects(self::exactly(2))->method('getValue')
            ->withConsecutive([['type' => 'string'], [base64_encode('fakeKey')], false], [['type' => 'string'], [], false])
            ->willReturnOnConsecutiveCalls('some key', 'some string');

        $payloadGenerator = new PayloadGenerator($schemaFieldValueResolver);

        self::assertSame([
            'some key' => 'some string'
        ], $payloadGenerator->generate([
            'type' => 'map',
            'values' => 'string'
        ]));
    }
}
