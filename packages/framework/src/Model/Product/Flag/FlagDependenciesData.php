<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagDependenciesData
{
    /**
     * @var bool
     */
    public bool $hasPromoCodeDependency;

    /**
     * @var bool
     */
    public bool $hasSeoMixDependency;

    /**
     * @var bool
     */
    public bool $hasPromotionXyDependency;
}
