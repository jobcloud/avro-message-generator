<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator;

use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;

/**
 * Interface AvroMessageGeneratorInterface
 */
interface AvroMessageGeneratorInterface
{
    /**
     * @param string $topicName
     * @param array<string, mixed> $predefinedPayload
     * @return string
     * @throws SchemaRegistryException
     */
    public function generateAvroMessageBody(string $topicName, array $predefinedPayload = []): string;

    /**
     * @param string $topicName
     * @param array<string, mixed> $predefinedPayload
     * @return string
     * @throws SchemaRegistryException
     */
    public function generateAvroMessageKey(string $topicName, array $predefinedPayload = []): string;
}
