<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\TwoLevelCache;

class TwoLevelCacheResetListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\TwoLevelCache\TwoLevelCacheProvider $twoLevelCacheProvider
     */
    public function __construct(
        protected readonly TwoLevelCacheProvider $twoLevelCacheProvider,
    ) {
    }

    public function onClear(): void
    {
        $this->twoLevelCacheProvider->reset();
    }
}
