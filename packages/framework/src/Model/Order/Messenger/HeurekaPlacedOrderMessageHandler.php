<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class HeurekaPlacedOrderMessageHandler
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessage $placedOrderMessage
     */
    public function __invoke(PlacedOrderMessage $placedOrderMessage): void
    {
        try {
            $orderSent = $this->orderFacade->sendHeurekaOrderInfo($placedOrderMessage->orderId);

            if ($orderSent === true) {
                $this->logger->info('Order successfully sent to Heureka', [
                    'orderId' => $placedOrderMessage->orderId,
                ]);
            }
        } catch (Exception $exception) {
            $this->logger->error('Sending order to Heureka failed', [
                'orderId' => $placedOrderMessage->orderId,
                'exception' => $exception,
            ]);
        }
    }
}
