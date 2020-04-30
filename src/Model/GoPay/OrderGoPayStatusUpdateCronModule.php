<?php

declare(strict_types = 1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\Order\OrderFacade;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class OrderGoPayStatusUpdateCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade
     */
    private $orderMailFacade;

    /**
     * @var \App\Model\GoPay\GoPayTransactionFacade
     */
    private $goPayTransactionFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\Model\GoPay\GoPayTransactionFacade $goPayTransactionFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        OrderFacade $orderFacade,
        OrderMailFacade $orderMailFacade,
        GoPayTransactionFacade $goPayTransactionFacade
    ) {
        $this->em = $em;
        $this->orderFacade = $orderFacade;
        $this->orderMailFacade = $orderMailFacade;
        $this->goPayTransactionFacade = $goPayTransactionFacade;
    }

    public function run(): void
    {
        $now = new DateTime();
        $twentyOneDaysAgo = $now->sub(DateInterval::createFromDateString('21 days'));
        $orders = $this->orderFacade->getAllUnpaidGoPayOrders($twentyOneDaysAgo);

        $this->logger->addInfo('Downloading status updates for `' . count($orders) . '` orders.');

        foreach ($orders as $order) {
            $this->logger->addInfo('Downloading GoPay status for order with ID `' . $order->getId() . '`.');

            if ($order->isDeleted()) {
                $this->logger->addInfo(sprintf(
                    'Order status of order with ID `%s` has not been changed because is deleted',
                    $order->getId()
                ));

                continue;
            }

            $oldOrderGoPayStatusIndexedByGoPaiId = $order->getGoPayTransactionsIndexedByGoPayId();
            $oldIsOrderPaid = $order->isGoPayPaid();

            try {
                $this->goPayTransactionFacade->updateOrderTransactions($order);
            } catch (GoPayPaymentDownloadException $e) {
                $this->logger->addError($e);

                continue;
            }

            foreach ($order->getGoPayTransactions() as $goPayTransaction) {
                $oldStatus = $oldOrderGoPayStatusIndexedByGoPaiId[$goPayTransaction->getGoPayId()];
                $newStatus = $goPayTransaction->getGoPayStatus();

                if ($oldStatus !== $newStatus) {
                    $this->logger->info(
                        sprintf(
                            'Order with id `%d` changed GoPay status from `%s` to `%s`.',
                            $order->getId(),
                            $oldStatus,
                            $newStatus
                        )
                    );
                }
            }

            if ($oldIsOrderPaid !== $order->isGoPayPaid()) {
                $this->logger->info('Sending order e-mail.');
                $this->orderMailFacade->sendEmail($order);
            }
        }

        $this->em->flush();
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
}
