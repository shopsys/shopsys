<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Store\Store;

class DefaultStoreCannotBeDeletedException extends Exception
{
    public function __construct(Store $store)
    {
        parent::__construct(sprintf('Store with ID %d is the default store and cannot be deleted.', $store->getId()));
    }
}
