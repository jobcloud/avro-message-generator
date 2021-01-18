<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field;

/**
 * Interface DataDefinitionFieldInterface
 */
interface DataDefinitionFieldInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string|null
     */
    public function getCommand(): ?string;

    /**
     * @return array<integer, mixed>|null
     */
    public function getArguments(): ?array;
}
