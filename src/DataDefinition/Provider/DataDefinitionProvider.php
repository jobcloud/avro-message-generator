<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Provider;

use DirectoryIterator;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJson;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use JsonException;

/**
 * Class DataDefinitionProvider
 */
class DataDefinitionProvider implements DataDefinitionProviderInterface
{
    /** @var string */
    public const GLOBAL_DATA_DEFINITION_NAME = 'global';

    /** @var string */
    private const DATA_DEFINITION_FILES_EXTENSION = 'json';

    private string $rootDirPath;

    /** @var array<string, DataDefinitionInterface> */
    private array $dataDefinitions;

    /**
     * @param string $rootDirPath
     */
    public function __construct(string $rootDirPath)
    {
        $this->rootDirPath = trim($rootDirPath, '/');
        $this->dataDefinitions = [];
    }

    /**
     * @return void
     * @throws IncorrectDataDefinitionJson
     */
    public function load(): void
    {
        $files = new DirectoryIterator($this->rootDirPath);

        foreach ($files as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (!$fileInfo->isReadable()) {
                continue;
            }

            if(self::DATA_DEFINITION_FILES_EXTENSION !== $fileInfo->getExtension()) {
                continue;
            }

            $file = $fileInfo->openFile();

            $dataDefinitionJson = $file->fread($file->getSize());

            try {
                /** @var array<string|integer, mixed> $decodedDataDefinition */
                $decodedDataDefinition = json_decode($dataDefinitionJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new IncorrectDataDefinitionJson($e->getMessage());
            }

            $dataDefinitionName = $file->getBasename(sprintf('.%s', self::DATA_DEFINITION_FILES_EXTENSION));

            $this->dataDefinitions[$dataDefinitionName] = new DataDefinition(
                $decodedDataDefinition
            );// nas use factory here
        }
    }

    /**
     * @param string $dataDefinitionName
     * @return DataDefinition
     * @throws UnexistingDataDefinitionException
     */
    public function getDataDefinition(string $dataDefinitionName): DataDefinition
    {
        if (!isset($this->dataDefinitions[$dataDefinitionName])) {
            throw new UnexistingDataDefinitionException(
                sprintf('Data definition %s does not exist.', $dataDefinitionName)
            );
        }

        return $this->dataDefinitions[$dataDefinitionName];
    }
}
