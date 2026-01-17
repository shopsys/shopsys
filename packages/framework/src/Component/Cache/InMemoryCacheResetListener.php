<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cache;

class InMemoryCacheResetListener
{
    public function __construct(
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function onClear(): void
    {
        $this->inMemoryCache->reset();
    }
}
