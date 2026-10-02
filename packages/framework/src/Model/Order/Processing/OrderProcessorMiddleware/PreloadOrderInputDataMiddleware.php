<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\Preloader\OrderInputPreloaderFacade;

class PreloadOrderInputDataMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly OrderInputPreloaderFacade $orderInputPreloaderFacade,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $this->orderInputPreloaderFacade->preload($orderProcessingData->orderInput);

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
