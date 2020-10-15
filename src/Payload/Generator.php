<?php

declare(strict_types=1);

namespace Jobcloud\Avro\Message\Generator\Payload;

use Faker\Generator as Faker;

/**
 * Class Generator
 */
class Generator implements GeneratorInterface
{
    private Faker $faker;

    /**
     * @param Faker $faker
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }
}