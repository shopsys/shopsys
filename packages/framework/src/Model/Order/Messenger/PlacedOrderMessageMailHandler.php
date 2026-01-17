<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PlacedOrderMessageMailHandler
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly LoggerInterface $logger,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    public function __invoke(PlacedOrderMessage $placedOrderMessage): void
    {
        try {
            $order = $this->orderFacade->getById($placedOrderMessage->orderId);
            $orderStatusNew = $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_NEW);
            $this->orderMailFacade->sendEmail($order, $orderStatusNew);
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
