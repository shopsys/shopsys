<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class RecalculateGiftFlagsCronModule implements SimpleCronModuleInterface
{
    public function __construct(
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
    }

    #[Override]
    public function run(): void
    {
        $this->giftFlagSynchronizerFacade->refreshAllGiftPlans();
    }
}
