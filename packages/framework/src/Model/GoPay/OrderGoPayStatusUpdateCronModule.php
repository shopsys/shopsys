<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayStatusUpdateFailedException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class OrderGoPayStatusUpdateCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GoPayFacade $goPayFacade,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    #[Override]
    public function run(): void
    {
        $twentyOneDaysAgo = $this->clock->now()->modify('-21 days');
        $orders = $this->goPayFacade->getAllUnpaidGoPayOrders($twentyOneDaysAgo);
        $failedOrderIds = [];

        $this->logger->info('Downloading status updates for orders.', [
            'ordersCount' => count($orders),
        ]);

        foreach ($orders as $order) {
            $orderId = $order->getId();
            $this->logger->info('Downloading GoPay status for order', [
                'orderId' => $orderId,
            ]);

            if ($order->isDeleted()) {
                $this->logger->info('Order status of order has not been changed because the order is deleted', [
                    'orderId' => $orderId,
                ]);

                continue;
            }

            $oldOrderGoPayStatusesIndexedByGoPaiId = $order->getGoPayTransactionStatusesIndexedByGoPayId();
            $oldIsOrderPaid = $order->isPaid();

            try {
                if ($this->paymentServiceFacade->updatePaymentTransactionsByOrder($order)) {
                    $this->orderFacade->updatePaymentByLastPaymentTransaction($order);
                }
            } catch (GoPayPaymentDownloadException $e) {
                $this->logger->error($e->getMessage(), [
                    'exception' => $e,
                ]);
                $failedOrderIds[] = $orderId;

                continue;
            }

            foreach ($order->getGoPayTransactions() as $goPayTransaction) {
                $oldStatus = $oldOrderGoPayStatusesIndexedByGoPaiId[$goPayTransaction->getExternalPaymentIdentifier()];
                $newStatus = $goPayTransaction->getExternalPaymentStatus();

                if ($oldStatus !== $newStatus) {
                    $this->logger->info('Order changed GoPay status', [
                        'orderId' => $orderId,
                        'oldStatus' => $oldStatus,
                        'newStatus' => $newStatus,
                    ]);
                }
            }

            if ($oldIsOrderPaid === $order->isPaid()) {
                continue;
            }

            $this->logger->info('Sending order e-mail.', [
                'orderId' => $orderId,
            ]);

            $this->orderMailFacade->sendEmail($order, $order->getStatus());
        }

        $this->em->flush();

        if ($failedOrderIds !== []) {
            throw new GoPayStatusUpdateFailedException(sprintf(
                'GoPay status update failed for %d order(s).',
                count($failedOrderIds),
            ));
        }
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
