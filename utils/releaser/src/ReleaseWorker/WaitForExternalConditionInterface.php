<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

interface WaitForExternalConditionInterface
{
    /**
     * Short human-readable description of what is being waited for, e.g.
     * 'shopsys/php-image:v19.1.0 published to Docker Hub'.
     */
    public function describe(): string;

    /**
     * Returns true once the external condition is satisfied. May be called
     * repeatedly; implementations should be safe to retry and tolerant of
     * transient network errors.
     */
    public function check(): bool;

    /**
     * Number of seconds to wait between consecutive check() invocations.
     * Pick a value that respects the upstream API's rate limits and the
     * realistic time scale on which the condition is expected to flip.
     */
    public function pollIntervalSeconds(): int;

    /**
     * Per-attempt status snapshot. Should reflect the most recent check()
     * result so operators can see why the condition is not yet satisfied
     * without scrolling back to the initial describe() line.
     */
    public function progressDescription(): string;
}
