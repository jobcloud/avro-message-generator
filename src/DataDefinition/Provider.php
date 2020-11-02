<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\DataDefinition;

use Exception;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJson;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionFile;
use JsonException;

/**
 * Class Provider
 */
class Provider implements ProviderInterface
{
    private string $rootDirPath;

    private GlobalDataDefinitionApplierInterface $globalDataDefinitionApplier;

    private string $dataDefinitionFilesExtension;

    private string $globalDataDefinitionName;

    /** @var array<string|integer, mixed> */
    private array $globalDataDefinition;

    /**
     * @param string $rootDirPath
     * @param GlobalDataDefinitionApplierInterface $globalDataDefinitionApplier
     * @param string $dataDefinitionFilesExtension
     * @param string $globalDataDefinitionName
     */
    public function __construct(
        string $rootDirPath,
        GlobalDataDefinitionApplierInterface $globalDataDefinitionApplier,
        string $dataDefinitionFilesExtension = 'json',
        string $globalDataDefinitionName = 'global'
    ) {
        $this->rootDirPath = trim($rootDirPath, '/');
        $this->globalDataDefinitionApplier = $globalDataDefinitionApplier;
        $this->dataDefinitionFilesExtension = $dataDefinitionFilesExtension;
        $this->globalDataDefinitionName = $globalDataDefinitionName;

        try {
            $this->globalDataDefinition = $this->loadDataDefinition($globalDataDefinitionName);
        } catch (Exception $e) {
            $this->globalDataDefinition = [];
        }
    }

    /**
     * @param string $dataDefinitionName
     * @return array<string|integer, mixed>
     * @throws UnexistingDataDefinitionFile|IncorrectDataDefinitionJson
     */
    public function getDataDefinition(string $dataDefinitionName): array
    {
        $dataDefinition = $this->loadDataDefinition($dataDefinitionName);

        return $this->globalDataDefinitionApplier->applyGlobalDataDefinition(
            $dataDefinition,
            $this->globalDataDefinition
        );
    }

    /**
     * @param string $dataDefinitionName
     * @return array<string|integer, mixed>
     * @throws UnexistingDataDefinitionFile|IncorrectDataDefinitionJson
     */
    private function loadDataDefinition(string $dataDefinitionName): array
    {
        $dataDefinitionFilePath = sprintf(
            '%s/%s.%s',
            $this->rootDirPath,
            $dataDefinitionName,
            $this->dataDefinitionFilesExtension
        );

        if (false === file_exists($dataDefinitionFilePath) || false === is_file($dataDefinitionFilePath)) {
            throw new UnexistingDataDefinitionFile(sprintf(
                'File %s with extension %s does not exist inside the % directory.',
                $dataDefinitionName,
                $this->dataDefinitionFilesExtension,
                $this->rootDirPath
            ));
        }

        $dataDefinitionJson = file_get_contents($dataDefinitionFilePath);

        if (false === $dataDefinitionJson) {
            throw new IncorrectDataDefinitionJson(
                sprintf('File %s contains incorrect json data.', $dataDefinitionFilePath)
            );
        }

        try {
            $dataDefinition = json_decode($dataDefinitionJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new IncorrectDataDefinitionJson($e->getMessage());
        }

        return $dataDefinition;
    }
}
