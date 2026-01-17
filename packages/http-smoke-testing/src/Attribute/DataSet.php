<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DataSet
{
    /**
     * @param \Shopsys\HttpSmokeTesting\Attribute\Parameter[] $parameters
     */
    public function __construct(
        public int $statusCode = 200,
        public array $parameters = [],
    ) {
    }
}
