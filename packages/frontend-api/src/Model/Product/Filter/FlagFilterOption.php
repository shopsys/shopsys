<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class FlagFilterOption
{
    public Flag $flag;

    public int $count;

    public bool $isAbsolute;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @param int $count
     * @param bool $isAbsolute
     */
    public function __construct(Flag $flag, int $count, bool $isAbsolute)
    {
        $this->flag = $flag;
        $this->count = $count;
        $this->isAbsolute = $isAbsolute;
    }
}
