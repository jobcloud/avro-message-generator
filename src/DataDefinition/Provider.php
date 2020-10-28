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

    private string $dataDefinitionFilesExtension;

    private string $globalDataDefinitionName;

    /** @var array<string, mixed> */
    private array $globalDataDefinition;

    /**
     * @param string $rootDirPath
     * @param string $dataDefinitionFilesExtension
     * @param string $globalDataDefinitionName
     */
    public function __construct(
        string $rootDirPath,
        string $dataDefinitionFilesExtension = 'json',
        string $globalDataDefinitionName = 'global'
    ) {
        $this->rootDirPath = trim($rootDirPath, '/');
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
     * @return array<string, mixed>
     * @throws UnexistingDataDefinitionFile|IncorrectDataDefinitionJson
     */
    public function getDataDefinition(string $dataDefinitionName): array
    {
        $dataDefinition = $this->loadDataDefinition($dataDefinitionName);

        return $this->applyGlobalDataDefinition($dataDefinition);
    }

    /**
     * @param string $dataDefinitionName
     * @return array<string, mixed>
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

    /**
     * @param array<string, mixed> $dataDefinition
     * @return array<string, mixed>
     */
    private function applyGlobalDataDefinition(array $dataDefinition): array
    {
        if ([] === $dataDefinition || [] === $this->globalDataDefinition) {
            return $dataDefinition;
        }

        $mappedGlobalDataDefinition = [];

        foreach ($this->globalDataDefinition as $globalDataDefinitionField) {
            if (!isset($globalDataDefinitionField['name'])) {
                continue;
            }

            $mappedGlobalDataDefinition[$globalDataDefinitionField['name']] = $globalDataDefinitionField;
        }

        if ([] === $mappedGlobalDataDefinition) {
            return $dataDefinition;
        }

        foreach ($dataDefinition as $key => $dataDefField) {
            if (isset($dataDefField['name']) && isset($mappedGlobalDataDefinition[$dataDefField['name']])) {
                $dataDefinition[$key] = $mappedGlobalDataDefinition[$dataDefField['name']];

                continue;
            }

            if (isset($dataDefField['definitions']) && is_array($dataDefField['definitions'])) {
                $dataDefinition[$key]['definitions'] = $this->applyGlobalDataDefinition($dataDefField['definitions']);
            }
        }

        return $dataDefinition;
    }
}
