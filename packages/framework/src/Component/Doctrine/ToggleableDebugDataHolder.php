<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Override;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Symfony\Bridge\Doctrine\Middleware\Debug\Query;

/**
 * Decorates the DoctrineBundle debug data holder so that SQL logging can be temporarily switched off
 * (e.g. during data fixtures or large imports to prevent memory exhaustion).
 * All calls are delegated to the decorated holder so that its features (e.g. `profiling_collect_backtrace`) keep working.
 */
class ToggleableDebugDataHolder extends DebugDataHolder
{
    protected bool $enabled = true;

    public function __construct(
        protected readonly DebugDataHolder $innerDebugDataHolder,
    ) {
    }

    #[Override]
    public function addQuery(string $connectionName, Query $query): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->innerDebugDataHolder->addQuery($connectionName, $query);
    }

    #[Override]
    public function getData(): array
    {
        return $this->innerDebugDataHolder->getData();
    }

    #[Override]
    public function reset(): void
    {
        $this->innerDebugDataHolder->reset();
    }

    public function disable(): void
    {
        $this->reset();
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }
}
