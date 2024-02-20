<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\ParameterGroup;
use App\Model\Product\Unit\Unit;

class ParameterDataFixtureData
{
    public function __construct(
        public string $name,
        public ?string $parameterType = null,
        public array $asFilterInCategories = [],
        public ?string $akeneoType = null,
        public ?ParameterGroup $parameterGroup = null,
        public ?Unit $unit = null
    ) {
    }
}
