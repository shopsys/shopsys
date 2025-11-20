<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PlacedOrderMessageMailHandler
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessage $placedOrderMessage
     */
    public function __invoke(PlacedOrderMessage $placedOrderMessage): void
    {
        try {
            $order = $this->orderFacade->getById($placedOrderMessage->orderId);
            $this->orderMailFacade->sendEmail($order);
            $this->logger->info(
                'Email for new order prepared successfully',
                ['orderId' => $placedOrderMessage->orderId],
            );
        } catch (Exception $exception) {
            $this->logger->error(
                'Preparing email for new order failed',
                [
                    'orderId' => $placedOrderMessage->orderId,
                    'exception' => $exception,
                ],
            );

            throw $exception;
        }
    }
}
