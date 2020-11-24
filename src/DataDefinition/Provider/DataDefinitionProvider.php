<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition\Provider;

use DirectoryIterator;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinition;
use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactoryInterface;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJsonException;
use Jobcloud\Avro\Message\Generator\Exception\InvalidDataDefinitionFieldException;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use JsonException;
use RuntimeException;

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

    private DataDefinitionFactoryInterface $dataDefinitionFactory;

    /**
     * @param string $rootDirPath
     * @param DataDefinitionFactoryInterface $dataDefinitionFactory
     */
    public function __construct(
        string $rootDirPath,
        DataDefinitionFactoryInterface $dataDefinitionFactory
    ) {
        $this->rootDirPath = trim($rootDirPath, '/');
        $this->dataDefinitionFactory = $dataDefinitionFactory;
        $this->dataDefinitions = [];
    }

    /**
     * @return void
     * @throws IncorrectDataDefinitionJsonException|InvalidDataDefinitionFieldException
     */
    public function load(): void
    {
        $files = new DirectoryIterator($this->rootDirPath);

        foreach ($files as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (self::DATA_DEFINITION_FILES_EXTENSION !== $fileInfo->getExtension()) {
                continue;
            }

            try {
                $file = $fileInfo->openFile();
            } catch (RuntimeException $e) {
                throw new IncorrectDataDefinitionJsonException(
                    sprintf('Missing read permission for data definition json file: "%s".', $fileInfo->getBasename())
                );
            }

            if ($file->getSize() === 0) {
                throw new IncorrectDataDefinitionJsonException(
                    sprintf('Empty data definition json file: "%s".', $file->getBasename())
                );
            }

            $dataDefinitionJson = $file->fread($file->getSize());

            $dataDefinitionJson = $dataDefinitionJson === false ? '' : $dataDefinitionJson;

            try {
                /** @var array<string|integer, mixed> $decodedDataDefinition */
                $decodedDataDefinition = json_decode($dataDefinitionJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new IncorrectDataDefinitionJsonException($e->getMessage());
            }

            $dataDefinitionName = $file->getBasename(sprintf('.%s', self::DATA_DEFINITION_FILES_EXTENSION));

            $this->dataDefinitions[$dataDefinitionName] = $this->dataDefinitionFactory->create(
                $decodedDataDefinition
            );
        }
    }

    /**
     * @param string $dataDefinitionName
     * @return DataDefinitionInterface
     * @throws UnexistingDataDefinitionException
     */
    public function getDataDefinition(string $dataDefinitionName): DataDefinitionInterface
    {
        if (!isset($this->dataDefinitions[$dataDefinitionName])) {
            throw new UnexistingDataDefinitionException(
                sprintf('Data definition "%s" does not exist.', $dataDefinitionName)
            );
        }

        return $this->dataDefinitions[$dataDefinitionName];
    }
}
