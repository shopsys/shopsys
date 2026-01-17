<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DataObject;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class ParameterDataFixtureData
{
    public function __construct(
        public string $name,
        public ?string $parameterType = null,
        public array $asFilterInCategories = [],
        public ?ParameterGroup $parameterGroup = null,
        public ?Unit $unit = null,
        public int $orderingPriority = 0,
    ) {
    }
}
