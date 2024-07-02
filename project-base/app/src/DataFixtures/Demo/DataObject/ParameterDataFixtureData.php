<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DataObject;

use App\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class ParameterDataFixtureData
{
    /**
     * @param string $name
     * @param string|null $parameterType
     * @param array $asFilterInCategories
     * @param \App\Model\Product\Parameter\ParameterGroup|null $parameterGroup
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null $unit
     */
    public function __construct(
        public string $name,
        public ?string $parameterType = null,
        public array $asFilterInCategories = [],
        public ?ParameterGroup $parameterGroup = null,
        public ?Unit $unit = null,
    ) {
    }
}
