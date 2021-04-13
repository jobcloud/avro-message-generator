<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator;

use Exception;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use Jobcloud\Avro\Message\Generator\Exception\MissingSchemaDefinitionException;
use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactoryInterface;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactoryInterface;
use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistryInterface;

class AvroMessageGenerator implements AvroMessageGeneratorInterface
{
    private RecordSerializer $recordSerializer;

    private AvroSchemaRegistryInterface $registry;

    private PayloadGeneratorFactoryInterface $payloadGeneratorFactory;

    private SchemaFieldValueResolverFactoryInterface $schemaFieldValueResolverFactory;

    /**
     * @param RecordSerializer $recordSerializer
     * @param AvroSchemaRegistryInterface $registry
     * @param PayloadGeneratorFactoryInterface $payloadGeneratorFactory
     * @param SchemaFieldValueResolverFactoryInterface $schemaFieldValueResolverFactory
     */
    public function __construct(
        RecordSerializer $recordSerializer,
        AvroSchemaRegistryInterface $registry,
        PayloadGeneratorFactoryInterface $payloadGeneratorFactory,
        SchemaFieldValueResolverFactoryInterface $schemaFieldValueResolverFactory
    ) {
        $this->recordSerializer = $recordSerializer;
        $this->registry = $registry;
        $this->payloadGeneratorFactory = $payloadGeneratorFactory;
        $this->schemaFieldValueResolverFactory = $schemaFieldValueResolverFactory;
    }

    /**
     * @param string $topicName
     * @param mixed $predefinedPayload
     * @return string
     * @throws SchemaRegistryException|Exception
     */
    public function generateAvroMessageBody(string $topicName, $predefinedPayload = null): string
    {
        $schema = $this->registry->getBodySchemaForTopic($topicName);

        return $this->generateAvroBinaryString($schema, $topicName, $predefinedPayload);
    }

    /**
     * @param string $topicName
     * @param mixed $predefinedPayload
     * @return string
     * @throws SchemaRegistryException|Exception
     */
    public function generateAvroMessageKey(string $topicName, $predefinedPayload = null): string
    {
        $schema = $this->registry->getKeySchemaForTopic($topicName);

        return $this->generateAvroBinaryString($schema, $topicName, $predefinedPayload);
    }

    /**
     * @param KafkaAvroSchemaInterface $schema
     * @param string $topicName
     * @param mixed $predefinedPayload
     * @return string
     * @throws SchemaRegistryException|Exception
     */
    private function generateAvroBinaryString(
        KafkaAvroSchemaInterface $schema,
        string $topicName,
        $predefinedPayload
    ): string {
        $schemaDefinition = $schema->getDefinition();

        if (null === $schemaDefinition) {
            throw new MissingSchemaDefinitionException(
                sprintf('Was unable to load definition for schema %s', $schema->getName())
            );
        }

        $decodedSchema = json_decode((string) $schemaDefinition, true, 512, JSON_THROW_ON_ERROR);

        $schemaFieldValueResolver = $this->schemaFieldValueResolverFactory->create($topicName, $predefinedPayload);

        $payloadGenerator = $this->payloadGeneratorFactory->create($schemaFieldValueResolver);

        $payload = $payloadGenerator->generate($decodedSchema);

        return $this->recordSerializer->encodeRecord(
            $schema->getName(),
            $schemaDefinition,
            $payload
        );
    }
}
