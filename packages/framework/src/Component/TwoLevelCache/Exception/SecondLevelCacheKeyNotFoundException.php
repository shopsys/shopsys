<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\TwoLevelCache\Exception;

class SecondLevelCacheKeyNotFoundException extends TwoLevelCacheException
{
    /**
     * @param string $firstLevelKey
     * @param string|int $secondLevelKey
     */
    public function __construct(string $firstLevelKey, string|int $secondLevelKey)
    {
        parent::__construct(sprintf('Value with keys "%s" and "%s" not found', $firstLevelKey, (string)$secondLevelKey));
    }
}
