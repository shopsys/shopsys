<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class RecalculateGiftFlagsCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade
     */
    public function __construct(
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
    ) {
    }

    /**
     * @param \Monolog\Logger $logger
     */
    #[Override]
    public function setLogger(Logger $logger)
    {
    }

    #[Override]
    public function run()
    {
        $this->giftFlagSynchronizerFacade->refreshAllGiftPlans();

        return true;
    }
}
