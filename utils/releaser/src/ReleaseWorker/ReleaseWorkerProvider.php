<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use LogicException;

class ReleaseWorkerProvider
{
    /**
     * @param iterable $releaseWorkers
     */
    public function __construct(
        private readonly iterable $releaseWorkers,
    ) {
    }

    /**
     * @param string|null $stage
     * @param int $step
     * @return \Shopsys\Releaser\ReleaseWorker\StageWorkerInterface[]
     */
    public function provideByStage(?string $stage, int $step): array
    {
        $activeReleaseWorkers = [];

        foreach ($this->releaseWorkers as $releaseWorker) {
            if (!$releaseWorker instanceof StageWorkerInterface) {
                continue;
            }

            if (!$releaseWorker->belongToStage($stage)) {
                continue;
            }

            $activeReleaseWorkers[] = $releaseWorker;
        }

        $configFileName = dirname(__DIR__, 2) . '/config/' . $stage . '.php';

        if (!file_exists($configFileName)) {
            throw new LogicException(sprintf('Config file "%s" used to provide order of release workers for stage "%s" was not found.', $configFileName, $stage));
        }

        $stageWorkers = require $configFileName;

        usort($activeReleaseWorkers, function ($a, $b) use ($stageWorkers) {
            $posA = array_search(get_class($a), $stageWorkers, true);
            $posB = array_search(get_class($b), $stageWorkers, true);

            return $posA <=> $posB;
        });

        if ($step > 0) {
            $activeReleaseWorkers = array_slice($activeReleaseWorkers, $step);
        }

        return $activeReleaseWorkers;
    }
}
