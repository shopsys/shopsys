<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Monolog\Logger;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class RemoveOldProductListsCronModule implements SimpleCronModuleInterface
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
    }

    #[Override]
    public function run(): void
    {
        $this->productListFacade->removeOldAnonymousProductLists($this->clock->now()->modify('-31 day'));
    }
}
