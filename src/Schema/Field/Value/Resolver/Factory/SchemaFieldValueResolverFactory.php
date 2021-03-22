<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory;

use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProvider;
use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProviderInterface;
use Jobcloud\Avro\Message\Generator\Exception\UnexistingDataDefinitionException;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolver;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\SchemaFieldValueResolverInterface;
use Faker\Generator as Faker;

class SchemaFieldValueResolverFactory implements SchemaFieldValueResolverFactoryInterface
{
    private Faker $faker;

    private DataDefinitionProviderInterface $dataDefinitionProvider;

    /**
     * @param Faker $faker
     * @param DataDefinitionProviderInterface $dataDefinitionProvider
     */
    public function __construct(Faker $faker, DataDefinitionProviderInterface $dataDefinitionProvider)
    {
        $this->faker = $faker;
        $this->dataDefinitionProvider = $dataDefinitionProvider;
    }

    /**
     * @param string $topicName
     * @param mixed $predefinedPayload
     * @return SchemaFieldValueResolverInterface
     */
    public function create(string $topicName, $predefinedPayload): SchemaFieldValueResolverInterface
    {
        try {
            $dataDefinition = $this->dataDefinitionProvider->getDataDefinition($topicName);
        } catch (UnexistingDataDefinitionException $e) {
            $dataDefinition = null;
        }

        try {
            $globalDataDefiniton = $this->dataDefinitionProvider->getDataDefinition(
                DataDefinitionProvider::GLOBAL_DATA_DEFINITION_NAME
            );
        } catch (UnexistingDataDefinitionException $e) {
            $globalDataDefiniton = null;
        }

        return new SchemaFieldValueResolver(
            $this->faker,
            $dataDefinition,
            $globalDataDefiniton,
            $predefinedPayload
        );
    }
}
