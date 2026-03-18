<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock\Exception;

use Exception;

class DefaultStockNotEnabledException extends Exception
{
    public function __construct(int $domainId)
    {
        parent::__construct(sprintf('Stock cannot be set as default on domain %d where it is not enabled.', $domainId));
    }
}
