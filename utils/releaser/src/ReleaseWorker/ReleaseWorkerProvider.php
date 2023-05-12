<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;

class ReleaseWorkerProvider
{
    /**
     * @param \Shopsys\Releaser\ReleaseWorker\StageWorkerInterface[] $releaseWorkers
     */
    public function __construct(private readonly array $releaseWorkers)
    {
    }

    /**
     * @return \Shopsys\Releaser\ReleaseWorker\StageWorkerInterface[]
     */
    public function provide(): array
    {
        return $this->releaseWorkers;
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
            if (!$releaseWorker instanceof StageAwareInterface) {
                continue;
            }

            if ($stage !== $releaseWorker->getStage()) {
                continue;
            }

            $activeReleaseWorkers[] = $releaseWorker;
        }

        if ($step > 0) {
            $activeReleaseWorkers = array_slice($activeReleaseWorkers, $step);
        }

        return $activeReleaseWorkers;
    }
}
