<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Exception;

class UnknownProductRecalculationPriorityException extends Exception
{
    /**
     * @param string $productRecalculationPriority
     */
    public function __construct(string $productRecalculationPriority)
    {
        parent::__construct(sprintf('Unknown product recalculation priority "%s"', $productRecalculationPriority));
    }
}
