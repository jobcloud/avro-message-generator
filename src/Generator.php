<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator;

use Exception;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use Jobcloud\Avro\Message\Generator\Exception\MissingSchemaDefinitionException;
use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistryInterface;
use Jobcloud\Avro\Message\Generator\Payload\GeneratorInterface as PayloadGeneratorInterface;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private RecordSerializer $recordSerializer;

    private AvroSchemaRegistryInterface $registry;

    private PayloadGeneratorInterface $payloadGenerator;

    /**
     * @param RecordSerializer $recordSerializer
     * @param AvroSchemaRegistryInterface $registry
     * @param PayloadGeneratorInterface $payloadGenerator
     */
    public function __construct(
        RecordSerializer $recordSerializer,
        AvroSchemaRegistryInterface $registry,
        PayloadGeneratorInterface $payloadGenerator
    ) {
        $this->recordSerializer = $recordSerializer;
        $this->registry = $registry;
        $this->payloadGenerator = $payloadGenerator;
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

        return $this->generateAvroBinaryString($schema, $predefinedPayload);
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

        return $this->generateAvroBinaryString($schema, $predefinedPayload);
    }

    /**
     * @param KafkaAvroSchemaInterface $schema
     * @param mixed $predefinedPayload
     * @return string
     * @throws SchemaRegistryException|Exception
     */
    private function generateAvroBinaryString(KafkaAvroSchemaInterface $schema, $predefinedPayload): string
    {
        $schemaDefinition = $schema->getDefinition();

        if (null === $schemaDefinition) {
            throw new MissingSchemaDefinitionException(
                sprintf('Was unable to load definition for schema %s', $schema->getName())
            );
        }

        $decodedSchema = json_decode((string) $schemaDefinition, true, 512, JSON_THROW_ON_ERROR);

        $payload = $this->payloadGenerator->generate($decodedSchema, $predefinedPayload);

        return $this->recordSerializer->encodeRecord(
            $schema->getName(),
            $schemaDefinition,
            $payload
        );
    }
}
