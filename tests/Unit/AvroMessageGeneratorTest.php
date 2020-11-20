<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit;

use AvroSchema;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use Jobcloud\Avro\Message\Generator\AvroMessageGenerator;
use Jobcloud\Avro\Message\Generator\Exception\MissingSchemaDefinitionException;
use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactoryInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactoryInterface;
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
}
