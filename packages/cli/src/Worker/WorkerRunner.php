<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

use Shopsys\Cli\Config\CoreProjectConfig;
use Throwable;

final class WorkerRunner
{
    /**
     * @var array<\Shopsys\Cli\Worker\WorkerInterface>
     */
    private array $workers = [];

    /**
     * @param iterable<\Shopsys\Cli\Worker\WorkerInterface> $workers
     */
    public function __construct(
        iterable $workers,
    ) {
        $this->loadWorkers($workers);
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
     * @param callable(\Shopsys\Cli\Worker\WorkerInterface, \Shopsys\Cli\Worker\WorkerResult): void $progressCallback
     * @return array<\Shopsys\Cli\Worker\WorkerResult>
     */
    public function run(
        CoreProjectConfig $config,
        string $projectPath,
        ?callable $progressCallback = null,
    ): array {
        $results = [];

        foreach ($this->workers as $worker) {
            try {
                $result = $worker->execute($config, $projectPath);
            } catch (Throwable $e) {
                $result = WorkerResult::failure(sprintf(
                    'Error in %s: %s',
                    $worker->getName(),
                    $e->getMessage(),
                ));
            }

            $results[] = $result;

            if ($progressCallback !== null) {
                $progressCallback($worker, $result);
            }
        }

        return $results;
    }

    /**
     * @return array<\Shopsys\Cli\Worker\WorkerInterface>
     */
    public function getWorkers(): array
    {
        return $this->workers;
    }

    /**
     * @param array<\Shopsys\Cli\Worker\WorkerResult> $results
     */
    public function allSuccessful(array $results): bool
    {
        foreach ($results as $result) {
            if (!$result->success) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<\Shopsys\Cli\Worker\WorkerResult> $results
     * @return array<string>
     */
    public function collectHints(array $results): array
    {
        $hints = [];

        foreach ($results as $result) {
            foreach ($result->hints as $hint) {
                $hints[] = $hint;
            }
        }

        return $hints;
    }
}
