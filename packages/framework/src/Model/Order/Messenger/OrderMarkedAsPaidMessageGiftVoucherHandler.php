<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherGenerationFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Mail\GiftVoucherMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderMarkedAsPaidMessageGiftVoucherHandler
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly GiftVoucherGenerationFacade $giftVoucherGenerationFacade,
        protected readonly GiftVoucherMailFacade $giftVoucherMailFacade,
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderMarkedAsPaidMessage $orderMarkedAsPaidMessage): void
    {
        try {
            $order = $this->orderFacade->getById($orderMarkedAsPaidMessage->orderId);
            $giftVouchers = $this->giftVoucherGenerationFacade->generateForOrder($order);

            if ($giftVouchers === [] || $this->isEmailAlreadyEnqueued($giftVouchers)) {
                return;
            }

            $this->giftVoucherMailFacade->sendGiftVouchersMail($order, $giftVouchers);
            $this->giftVoucherFacade->markEmailAsEnqueued($giftVouchers);
            $this->logger->info(
                'Gift vouchers generated and email prepared successfully',
                [
                    'orderId' => $orderMarkedAsPaidMessage->orderId,
                    'giftVoucherCount' => count($giftVouchers),
                ],
            );
        } catch (Exception $exception) {
            $this->logger->error(
                'Generating gift vouchers for paid order failed',
                [
                    'orderId' => $orderMarkedAsPaidMessage->orderId,
                    'exception' => $exception,
                ],
            );

            throw $exception;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[] $giftVouchers
     */
    protected function isEmailAlreadyEnqueued(array $giftVouchers): bool
    {
        foreach ($giftVouchers as $giftVoucher) {
            if ($giftVoucher->getEmailEnqueuedAt() === null) {
                return false;
            }
        }

        return true;
    }
}
