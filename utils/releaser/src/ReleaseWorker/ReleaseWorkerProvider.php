<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;

class ReleaseWorkerProvider
{
    /**
     * @var \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[]
     */
    private array $releaseWorkers;

    /**
     * @param \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[] $releaseWorkers
     */
    public function __construct(array $releaseWorkers)
    {
        $this->releaseWorkers = $releaseWorkers;
    }

    /**
     * @return \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[]
     */
    public function provide(): array
    {
        return $this->releaseWorkers;
    }

    /**
     * @param string|null $stage
     * @param int $step
     * @return \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[]
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
