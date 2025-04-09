<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Shopsys\MakerBundle\EntityConfig\EntityConfigFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseMakerDependency
{
    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \Shopsys\MakerBundle\EntityConfig\EntityConfigFactory $entityConfigFactory
     */
    public function __construct(
        public readonly KernelInterface $kernel,
        public readonly EntityConfigFactory $entityConfigFactory,
    ) {
    }
}
