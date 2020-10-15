<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use Jobcloud\Avro\Message\Generator\Schema\FieldsExtractorInterface;
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

    private FieldsExtractorInterface $fieldsExtractor;

    /**
     * @param RecordSerializer $recordSerializer
     * @param AvroSchemaRegistryInterface $registry
     * @param PayloadGeneratorInterface $payloadGenerator
     * @param FieldsExtractorInterface $fieldsExtractor
     */
    public function __construct(
        RecordSerializer $recordSerializer,
        AvroSchemaRegistryInterface $registry,
        PayloadGeneratorInterface $payloadGenerator,
        FieldsExtractorInterface $fieldsExtractor
    ) {
        $this->recordSerializer = $recordSerializer;
        $this->registry = $registry;
        $this->payloadGenerator = $payloadGenerator;
        $this->fieldsExtractor = $fieldsExtractor;
    }

    /**
     * @param string $topicName
     * @param array<string, mixed> $predefinedPayload
     * @return string
     * @throws SchemaRegistryException
     */
    public function generateAvroMessageBody(string $topicName, array $predefinedPayload = []): string
    {
        $schema = $this->registry->getBodySchemaForTopic($topicName);

        return $this->generateAvroBinaryString($schema, $predefinedPayload);
    }

    /**
     * @param string $topicName
     * @param array<string, mixed> $predefinedPayload
     * @return string
     * @throws SchemaRegistryException
     */
    public function generateAvroMessageKey(string $topicName, array $predefinedPayload = []): string
    {
        $schema = $this->registry->getKeySchemaForTopic($topicName);

        return $this->generateAvroBinaryString($schema, $predefinedPayload);
    }

    /**
     * @param KafkaAvroSchemaInterface $schema
     * @param array<string, mixed> $predefinedPayload
     * @return string
     */
    private function generateAvroBinaryString(KafkaAvroSchemaInterface $schema, array $predefinedPayload): string
    {
        $fields = $this->fieldsExtractor->extract($schema);

        var_dump($fields);// nas
    }
}