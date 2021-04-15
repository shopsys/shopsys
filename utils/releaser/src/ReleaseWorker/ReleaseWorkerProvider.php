<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Nette\Utils\Strings;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;
use Symplify\MonorepoBuilder\Release\Exception\ConflictingPriorityException;

class ReleaseWorkerProvider
{
    /**
     * @var \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[]
     */
    private $releaseWorkersByPriority = [];

    /**
     * @param \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[] $releaseWorkers
     * @param bool $enableDefaultReleaseWorkers
     */
    public function __construct(array $releaseWorkers, bool $enableDefaultReleaseWorkers)
    {
        $this->setWorkersAndSortByPriority($releaseWorkers, $enableDefaultReleaseWorkers);
    }

    /**
     * @param string|null $stage
     * @param int $step
     * @return \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[]
     */
    public function provideByStage(?string $stage, int $step): array
    {
        if ($stage === null) {
            return $this->releaseWorkersByPriority;
        }

        $activeReleaseWorkers = [];
        foreach ($this->releaseWorkersByPriority as $releaseWorker) {
            if ($releaseWorker instanceof StageAwareInterface) {
                if ($stage === $releaseWorker->getStage()) {
                    $activeReleaseWorkers[] = $releaseWorker;
                }
            }
        }

        if ($step > 0) {
            $activeReleaseWorkers = array_slice($activeReleaseWorkers, $step);
        }

        return $activeReleaseWorkers;
    }

    /**
     * @param \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface[] $releaseWorkers
     * @param bool $enableDefaultReleaseWorkers
     */
    private function setWorkersAndSortByPriority(array $releaseWorkers, bool $enableDefaultReleaseWorkers): void
    {
        foreach ($releaseWorkers as $releaseWorker) {
            if ($this->shouldSkip($releaseWorker, $enableDefaultReleaseWorkers)) {
                continue;
            }

            $priority = $releaseWorker->getPriority();
            if (isset($this->releaseWorkersByPriority[$priority])) {
                throw new ConflictingPriorityException($releaseWorker, $this->releaseWorkersByPriority[$priority]);
            }

            $this->releaseWorkersByPriority[$priority] = $releaseWorker;
        }

        krsort($this->releaseWorkersByPriority);
    }

    /**
     * @param \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface $releaseWorker
     * @param bool $enableDefaultReleaseWorkers
     * @return bool
     */
    private function shouldSkip(ReleaseWorkerInterface $releaseWorker, bool $enableDefaultReleaseWorkers): bool
    {
        if ($enableDefaultReleaseWorkers) {
            return false;
        }

        return Strings::startsWith(get_class($releaseWorker), 'Symplify\MonorepoBuilder\Release');
    }
}
