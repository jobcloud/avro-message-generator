<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema;

use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use JsonException;

/**
 * Class FieldsExtractor
 */
class FieldsExtractor implements FieldsExtractorInterface
{
    /**
     * @param KafkaAvroSchemaInterface $schema
     * @return array
     */
    public function extract(KafkaAvroSchemaInterface $schema): array
    {
        try {
            $decodedSchema = json_decode((string) $schema->getDefinition(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // nas
        }

        if (!isset($decodedSchema['fields'])) {
            // nas
        }

        return $decodedSchema['fields'];
    }
}