<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

use Override;
use ReflectionClass;

abstract class AbstractWorker implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return (new ReflectionClass(static::class))->getShortName();
    }
}
