<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use DateTimeImmutable;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class RemoveOldProductListsCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     */
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
    }

    public function run(): void
    {
        $this->productListFacade->removeOldAnonymousProductLists(new DateTimeImmutable('-31day'));
    }
}
