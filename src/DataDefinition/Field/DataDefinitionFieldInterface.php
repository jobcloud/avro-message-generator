<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Field;

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
     * @return array<integer, mixed>
     */
    public function getArguments(): array;

    /**
     * @return bool
     */
    public function isValueField(): bool;

    /**
     * @return bool
     */
    public function isCommandField(): bool;
}
