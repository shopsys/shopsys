<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\TwoLevelCache\Exception;

class FirstLevelCacheKeyNotFoundException extends TwoLevelCacheException
{
    /**
     * @param string $firstLevelKey
     */
    public function __construct(string $firstLevelKey)
    {
        parent::__construct(sprintf('First level cache key "%s" not found', $firstLevelKey));
    }
}
