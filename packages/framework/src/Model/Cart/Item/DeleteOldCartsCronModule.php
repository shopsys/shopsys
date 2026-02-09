<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteOldCartsCronModule implements SimpleCronModuleInterface
{
    public function __construct(protected readonly CartFacade $cartFacade)
    {
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
        $this->cartFacade->deleteOldCarts();
    }
}
