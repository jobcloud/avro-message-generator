<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Provider;

use Jobcloud\Avro\Message\Generator\DataDefinition\DataDefinitionInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactoryInterface;
use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProvider;
use Jobcloud\Avro\Message\Generator\Exception\IncorrectDataDefinitionJsonException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProvider
 */
class DataDefinitionProviderTest extends TestCase
{
    public function setUp(): void
    {
        if (false === file_exists($this->getTestDefinitionFilePath())) {
            $this->setContent('');
        } else {
            $this->setMode(0777);
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (true === file_exists('./tests/Unit/Definitions/example.definition.txt')) {
            unlink('./tests/Unit/Definitions/example.definition.txt');
        }
    }

    public function testEmptyDataDefinition(): void
    {
        $this->setContent('');

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $dataDefinitionFactory->expects(self::never())->method('create');

        self::expectExceptionMessage('Empty data definition json file: "example.definition.json".');

        $provider->load();
    }

    public function testIncorrectDataDefinition(): void
    {
        $this->setContent('test');

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $dataDefinitionFactory->expects(self::never())->method('create');

        self::expectException(IncorrectDataDefinitionJsonException::class);

        $provider->load();
    }

    public function testCorrectDataDefinition(): void
    {
        $this->setContent('{"test":"test"}');

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
        ->getMock();

        $dataDefinitionFactory->expects(self::once())->method('create')->with(['test' => 'test']);

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $provider->load();

        self::assertInstanceOf(DataDefinitionInterface::class, $provider->getDataDefinition('example.definition'));
    }

    public function testUnexistingDataDefinition(): void
    {
        $this->setContent('{"test":"test"}');

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $dataDefinitionFactory->expects(self::once())->method('create')->with(['test' => 'test']);

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $provider->load();

        self::expectExceptionMessage('Data definition "unexisting.definition" does not exist.');

        $provider->getDataDefinition('unexisting.definition');
    }

    public function testLoadFileWithWrongExtension(): void
    {
        unlink($this->getTestDefinitionFilePath());

        file_put_contents('./tests/Unit/Definitions/example.definition.txt', '');

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $dataDefinitionFactory->expects(self::never())->method('create');

        $provider->load();
    }

    public function testLoadFileWithIncorrectPremissions(): void
    {
        $this->setContent('');

        $this->setMode(0333);

        /** @var DataDefinitionFactoryInterface|MockObject $dataDefinitionFactory */
        $dataDefinitionFactory = $this->getMockBuilder(DataDefinitionFactoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $provider = new DataDefinitionProvider('./tests/Unit/Definitions', $dataDefinitionFactory);

        $dataDefinitionFactory->expects(self::never())->method('create');

        self::expectExceptionMessage(
            'Missing read permission for data definition json file: "example.definition.json".'
        );

        $provider->load();
    }

    private function setMode(int $mode): void
    {
        chmod($this->getTestDefinitionFilePath(), $mode);
    }

    private function setContent($content): void
    {
        file_put_contents($this->getTestDefinitionFilePath(), $content);
    }

    private function getTestDefinitionFilePath(): string
    {
        return './tests/Unit/Definitions/example.definition.json';
    }
}
