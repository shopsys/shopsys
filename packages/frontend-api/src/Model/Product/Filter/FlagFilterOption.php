<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class FlagFilterOption
{
    public function __construct(
        public readonly Flag $flag,
        public readonly int $count,
        public readonly bool $isAbsolute,
        public bool $isSelected,
    ) {
    }
}
