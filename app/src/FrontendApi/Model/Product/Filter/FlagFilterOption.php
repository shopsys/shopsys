<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption as BaseFlagFilterOption;

/**
 * @property \App\Model\Product\Flag\Flag $flag
 */
class FlagFilterOption extends BaseFlagFilterOption
{
    /**
     * @var bool
     */
    public bool $isSelected;

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param int $count
     * @param bool $isAbsolute
     * @param bool $isSelected
     */
    public function __construct(Flag $flag, int $count, bool $isAbsolute, bool $isSelected)
    {
        parent::__construct($flag, $count, $isAbsolute);

        $this->isSelected = $isSelected;
    }
}
