<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\LocalCache;

class LocalCacheResetListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade $localCacheFacade
     */
    public function __construct(
        protected readonly LocalCacheFacade $localCacheFacade,
    ) {
    }

    public function onClear(): void
    {
        $this->localCacheFacade->reset();
    }
}
