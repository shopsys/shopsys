<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

final class WorkerRunner
{
    /**
     * @var array<\Shopsys\Cli\Worker\WorkerInterface>
     */
    private array $workers = [];

    /**
     * @param iterable<\Shopsys\Cli\Worker\WorkerInterface> $taggedWorkers
     */
    public function __construct(
        iterable $taggedWorkers,
    ) {
        $this->loadWorkers($taggedWorkers);
    }

    /**
     * @param iterable<\Shopsys\Cli\Worker\WorkerInterface> $taggedWorkers
     */
    private function loadWorkers(iterable $taggedWorkers): void
    {
        $workers = [];

        foreach ($taggedWorkers as $worker) {
            $workers[] = $worker;
        }

        // Sort workers by priority (higher priority runs first)
        usort(
            $workers,
            static fn (WorkerInterface $a, WorkerInterface $b) => $b->getPriority() <=> $a->getPriority(),
        );

        $this->workers = $workers;
    }

    /**
     * @return array<\Shopsys\Cli\Worker\WorkerInterface>
     */
    public function getWorkers(): array
    {
        return $this->workers;
    }
}
