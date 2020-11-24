<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\Payload\Factory;

use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactory;
use Jobcloud\Avro\Message\Generator\Payload\PayloadGeneratorInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactory
 */
class PayloadGeneratorFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        $payloadGeneratorFactory = new PayloadGeneratorFactory();

        self::assertInstanceOf(
            PayloadGeneratorInterface::class,
            $payloadGeneratorFactory->create($schemaFieldValueResolver)
        );
    }
}
