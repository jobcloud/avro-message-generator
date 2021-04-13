<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit;

use AvroIOTypeException;
use AvroSchema;
use Exception;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use Jobcloud\Avro\Message\Generator\AvroMessageGenerator;
use Jobcloud\Avro\Message\Generator\Exception\MissingSchemaDefinitionException;
use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactoryInterface;
use Jobcloud\Avro\Message\Generator\Payload\PayloadGeneratorInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactoryInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;
use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistryInterface;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\AvroMessageGenerator
 */
class AvroMessageGeneratorTest extends TestCase
{
    public function testGenerateAvroMessageBodyWithNullSchemaDefinition(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn(null);

        $registry->expects(self::once())->method('getBodySchemaForTopic')->willReturn($kafkaAvroSchema);

        self::expectException(MissingSchemaDefinitionException::class);

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageBody('testTopic');
    }

    public function testGenerateAvroMessageBodyWithInvalidJsonSchemaDefinition(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
        ->getMock();

        /** @var AvroSchema|MockObject $avroSchema */
        $avroSchema = $this->getMockBuilder(AvroSchema::class)
            ->disableOriginalConstructor()
        ->getMock();

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn($avroSchema);

        $registry->expects(self::once())->method('getBodySchemaForTopic')->willReturn($kafkaAvroSchema);

        self::expectException(JsonException::class);

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageBody('testTopic');
    }

    public function testGenerateAvroMessageBodyWithInvalidPayload(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encodeRecord'])
        ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
        ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        /** @var PayloadGeneratorInterface|MockObject $payloadGenerator */
        $payloadGenerator = $this->getMockBuilder(PayloadGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate'])
        ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
        ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AvroSchema|MockObject $avroSchema */
        $avroSchema = new AvroSchema('string');

        /** @var AvroIOTypeException|MockObject $exception */
        $exception = $this->getMockBuilder(AvroIOTypeException::class)
            ->disableOriginalConstructor()
        ->getMock();

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn($avroSchema);

        $registry->expects(self::once())->method('getBodySchemaForTopic')->willReturn($kafkaAvroSchema);

        $schemaFieldValueResolverFactory->expects(self::once())->method('create')
            ->with('marketplace.core.entity.credit', null)
            ->willReturn($schemaFieldValueResolver);

        $payloadGeneratorFactory->expects(self::once())->method('create')
            ->with($schemaFieldValueResolver)
            ->willReturn($payloadGenerator);

        $payloadGenerator->expects(self::once())->method('generate')->with(['type' => 'string'])->willReturn(null);

        $recordSerializer->expects(self::once())->method('encodeRecord')
            ->with('', $avroSchema, null)->willThrowException($exception);

        self::expectException(Exception::class);

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageBody('marketplace.core.entity.credit');
    }

    public function testGenerateAvroMessageBodyWithPayloadWithoutPredefinedPayload(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encodeRecord'])
        ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var PayloadGeneratorInterface|MockObject $payloadGenerator */
        $payloadGenerator = $this->getMockBuilder(PayloadGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate'])
            ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AvroSchema|MockObject $avroSchema */
        $avroSchema = new AvroSchema('string');

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn($avroSchema);

        $registry->expects(self::once())->method('getBodySchemaForTopic')->willReturn($kafkaAvroSchema);

        $schemaFieldValueResolverFactory->expects(self::once())->method('create')
            ->with('marketplace.core.entity.credit', null)
            ->willReturn($schemaFieldValueResolver);

        $payloadGeneratorFactory->expects(self::once())->method('create')
            ->with($schemaFieldValueResolver)
            ->willReturn($payloadGenerator);

        $payloadGenerator->expects(self::once())->method('generate')->with(['type' => 'string'])->willReturn('');

        $recordSerializer->expects(self::once())->method('encodeRecord')
            ->with('', $avroSchema, '')->willReturn('');

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageBody('marketplace.core.entity.credit');
    }

    public function testGenerateAvroMessageBodyWithPayloadWithPredefinedPayload(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encodeRecord'])
            ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var PayloadGeneratorInterface|MockObject $payloadGenerator */
        $payloadGenerator = $this->getMockBuilder(PayloadGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate'])
            ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AvroSchema|MockObject $avroSchema */
        $avroSchema = new AvroSchema('string');

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn($avroSchema);

        $registry->expects(self::once())->method('getBodySchemaForTopic')->willReturn($kafkaAvroSchema);

        $schemaFieldValueResolverFactory->expects(self::once())->method('create')
            ->with('marketplace.core.entity.credit', 'test')
            ->willReturn($schemaFieldValueResolver);

        $payloadGeneratorFactory->expects(self::once())->method('create')
            ->with($schemaFieldValueResolver)
            ->willReturn($payloadGenerator);

        $payloadGenerator->expects(self::once())->method('generate')->with(['type' => 'string'])->willReturn('');

        $recordSerializer->expects(self::once())->method('encodeRecord')
            ->with('', $avroSchema, '')->willReturn('');

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageBody('marketplace.core.entity.credit', 'test');
    }

    public function testGenerateAvroMessageKeyWithPayloadWithPredefinedPayload(): void
    {
        /** @var RecordSerializer|MockObject $recordSerializer */
        $recordSerializer = $this->getMockBuilder(RecordSerializer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encodeRecord'])
            ->getMock();

        /** @var AvroSchemaRegistryInterface|MockObject $registry */
        $registry = $this->getMockBuilder(AvroSchemaRegistryInterface::class)
            ->onlyMethods([
                'getBodySchemaForTopic',
                'addBodySchemaMappingForTopic',
                'addKeySchemaMappingForTopic',
                'getTopicSchemaMapping',
                'getKeySchemaForTopic',
                'hasBodySchemaForTopic',
                'hasKeySchemaForTopic'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayloadGeneratorFactoryInterface|MockObject $payloadGeneratorFactory */
        $payloadGeneratorFactory = $this->getMockBuilder(PayloadGeneratorFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var PayloadGeneratorInterface|MockObject $payloadGenerator */
        $payloadGenerator = $this->getMockBuilder(PayloadGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate'])
            ->getMock();

        /** @var SchemaFieldValueResolverFactoryInterface|MockObject $schemaFieldValueResolverFactory */
        $schemaFieldValueResolverFactory = $this->getMockBuilder(SchemaFieldValueResolverFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        /** @var SchemaFieldValueResolverInterface|MockObject $schemaFieldValueResolver */
        $schemaFieldValueResolver = $this->getMockBuilder(SchemaFieldValueResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var KafkaAvroSchemaInterface|MockObject $kafkaAvroSchema */
        $kafkaAvroSchema = $this->getMockBuilder(KafkaAvroSchemaInterface::class)
            ->onlyMethods([
                'getDefinition',
                'getName',
                'getVersion',
                'setDefinition'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AvroSchema|MockObject $avroSchema */
        $avroSchema = new AvroSchema('string');

        $kafkaAvroSchema->expects(self::once())->method('getDefinition')->willReturn($avroSchema);

        $registry->expects(self::once())->method('getKeySchemaForTopic')->willReturn($kafkaAvroSchema);

        $schemaFieldValueResolverFactory->expects(self::once())->method('create')
            ->with('marketplace.core.entity.credit', 'test')
            ->willReturn($schemaFieldValueResolver);

        $payloadGeneratorFactory->expects(self::once())->method('create')
            ->with($schemaFieldValueResolver)
            ->willReturn($payloadGenerator);

        $payloadGenerator->expects(self::once())->method('generate')->with(['type' => 'string'])->willReturn('');

        $recordSerializer->expects(self::once())->method('encodeRecord')
            ->with('', $avroSchema, '')->willReturn('');

        $generator = new AvroMessageGenerator(
            $recordSerializer,
            $registry,
            $payloadGeneratorFactory,
            $schemaFieldValueResolverFactory
        );

        $generator->generateAvroMessageKey('marketplace.core.entity.credit', 'test');
    }
}
