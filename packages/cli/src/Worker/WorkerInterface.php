<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

use Shopsys\Cli\Config\CoreProjectConfig;

interface WorkerInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult;

    /**
     * Higher priority workers run first
     */
    public function getPriority(): int;
}
