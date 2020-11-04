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
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionProviderInterface;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private RecordSerializer $recordSerializer;

    private AvroSchemaRegistryInterface $registry;

    private PayloadGeneratorInterface $payloadGenerator;

    private DataDefinitionProviderInterface $dataDefinitionProvider;

    /**
     * @param RecordSerializer $recordSerializer
     * @param AvroSchemaRegistryInterface $registry
     * @param PayloadGeneratorInterface $payloadGenerator
     */
    public function __construct(
        RecordSerializer $recordSerializer,
        AvroSchemaRegistryInterface $registry,
        PayloadGeneratorInterface $payloadGenerator,
        DataDefinitionProviderInterface $dataDefinitionProvider
    ) {
        $this->recordSerializer = $recordSerializer;
        $this->registry = $registry;
        $this->payloadGenerator = $payloadGenerator;
        $this->dataDefinitionProvider = $dataDefinitionProvider;
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

        $dataDefinition = $this->dataDefinitionProvider->getDataDefinition($topicName);

        $payload = $this->payloadGenerator->generate($decodedSchema, $dataDefinition, $predefinedPayload);

        return $this->recordSerializer->encodeRecord(
            $schema->getName(),
            $schemaDefinition,
            $payload
        );
    }
}
