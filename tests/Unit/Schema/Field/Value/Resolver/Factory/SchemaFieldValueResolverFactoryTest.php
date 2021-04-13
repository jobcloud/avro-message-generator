<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\Schema\Field\Value\Resolver\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProviderInterface;
use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactory;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactory
 */
class SchemaFieldValueResolverFactoryTest extends TestCase
{
    public function testCreateWithNullDefinitions(): void
    {
        /** @var DataDefinitionProviderInterface|MockObject $dataDefinitionProvider */
        $dataDefinitionProvider = $this->getMockBuilder(DataDefinitionProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataDefinition', 'load'])
        ->getMock();

        /** @var Faker|MockObject $faker */
        $faker = $this->getMockBuilder(Faker::class)
            ->disableOriginalConstructor()
        ->getMock();

        $exception = new UnexistingDataDefinitionException('');

        $dataDefinitionProvider->expects(self::exactly(2))->method('getDataDefinition')
            ->withConsecutive(['test'], ['global'])->willThrowException($exception);

        $schemaFieldValueResolverFactory = new SchemaFieldValueResolverFactory($faker, $dataDefinitionProvider);

        $schemaFieldValueResolver = $schemaFieldValueResolverFactory->create('test', null);

        self::assertInstanceOf(SchemaFieldValueResolverInterface::class, $schemaFieldValueResolver);
    }

    public function testCreateWithDefinitions(): void
    {
        /** @var DataDefinitionProviderInterface|MockObject $dataDefinitionProvider */
        $dataDefinitionProvider = $this->getMockBuilder(DataDefinitionProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataDefinition', 'load'])
        ->getMock();

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

        $dataDefinitionProvider->expects(self::exactly(2))->method('getDataDefinition')
            ->withConsecutive(['test'], ['global'])->willReturnOnConsecutiveCalls(
                $dataDefinition,
                $globalDataDefinition
            );

        $schemaFieldValueResolverFactory = new SchemaFieldValueResolverFactory($faker, $dataDefinitionProvider);

        $schemaFieldValueResolver = $schemaFieldValueResolverFactory->create('test', 'testPredefinedPayload');

        self::assertInstanceOf(SchemaFieldValueResolverInterface::class, $schemaFieldValueResolver);
    }
}
