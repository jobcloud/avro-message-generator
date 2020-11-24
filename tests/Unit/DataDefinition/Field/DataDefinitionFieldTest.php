<?php

namespace Jobcloud\Avro\Message\Generator\Tests\Unit\DataDefinition\Field;

use Faker\Factory;
use Faker\Generator as Faker;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jobcloud\Avro\Message\Generator\DataDefinition\Field\DataDefinitionField
 */
class DataDefinitionFieldTest extends TestCase
{
    public function testGetValueWithoutCommand(): void
    {
        $dataDefinitionField = new DataDefinitionField(['value' => 'test']);

        self::assertSame('test', $dataDefinitionField->getValue(null));
    }

    public function testGetValueWithCommandWithoutArguments(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        $dataDefinitionField = new DataDefinitionField(['command' => 'word']);

        self::assertIsString($dataDefinitionField->getValue($faker));
    }

    public function testGetValueWithCommandWitArguments(): void
    {
        /** @var Faker $faker */
        $faker = Factory::create();

        $dataDefinitionField = new DataDefinitionField(['command' => 'randomDigitNot', 'arguments' => [5]]);

        self::assertIsInt($dataDefinitionField->getValue($faker));
    }

    public function testGetValueWithCommandWithoutExecutor(): void
    {
        $dataDefinitionField = new DataDefinitionField(['command' => 'randomDigitNot', 'arguments' => [5]]);

        self::expectExceptionMessage('Missing executor for "randomDigitNot" command.');

        $dataDefinitionField->getValue(null);
    }
}
