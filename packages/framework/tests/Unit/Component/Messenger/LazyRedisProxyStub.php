<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger;

use Override;
use Redis;
use Symfony\Component\VarExporter\LazyObjectInterface;

class LazyRedisProxyStub extends Redis implements LazyObjectInterface
{
    public bool $closed = false;

    public function __construct(protected readonly bool $initialized)
    {
        parent::__construct();
    }

    #[Override]
    public function isLazyObjectInitialized(bool $partial = false): bool
    {
        return $this->initialized;
    }

    #[Override]
    public function initializeLazyObject(): object
    {
        return $this;
    }

    #[Override]
    public function resetLazyObject(): bool
    {
        return false;
    }

    #[Override]
    public function close(): bool
    {
        $this->closed = true;

        return true;
    }
}
