<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

use Shopsys\Cli\Model\ProjectConfiguration;

interface WorkerInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param \Shopsys\Cli\Model\ProjectConfiguration $config
     * @param string $projectPath
     * @return \Shopsys\Cli\Worker\WorkerResult
     */
    public function execute(ProjectConfiguration $config, string $projectPath): WorkerResult;

    /**
     * Higher priority workers run first
     *
     * @return int
     */
    public function getPriority(): int;
}
