<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfigFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseMakerDependency
{
    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfigFactory $entityConfigFactory
     */
    public function __construct(
        public readonly KernelInterface $kernel,
        public readonly EntityConfigFactory $entityConfigFactory,
    ) {
    }
}
