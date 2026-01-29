<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteOldCartsCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(protected readonly CartFacade $cartFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
    }

    public function run()
    {
        $this->cartFacade->deleteOldCarts();
    }
}
