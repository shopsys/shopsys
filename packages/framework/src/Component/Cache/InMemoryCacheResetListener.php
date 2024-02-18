<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cache;

class InMemoryCacheResetListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function onClear(): void
    {
        $this->inMemoryCache->reset();
    }
}
