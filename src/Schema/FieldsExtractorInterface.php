<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema;

use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;

/**
 * Interface FieldsExtractorInterface
 */
interface FieldsExtractorInterface
{
    /**
     * @param KafkaAvroSchemaInterface $schema
     * @return array
     */
    public function extract(KafkaAvroSchemaInterface $schema): array;
}