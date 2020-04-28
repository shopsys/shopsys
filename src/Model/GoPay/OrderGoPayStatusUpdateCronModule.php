<?php

declare(strict_types = 1);

namespace App\Model\GoPay;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\Order\OrderFacade;
use Symfony\Bridge\Monolog\Logger;

class OrderGoPayStatusUpdateCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Doctrine\ORM\EntityManager
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
     * @var \App\Model\GoPay\GoPayOnCurrentDomainFacade
     */
    private $goPayFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\Model\GoPay\GoPayOnCurrentDomainFacade $goPayFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        OrderFacade $orderFacade,
        OrderMailFacade $orderMailFacade,
        GoPayOnCurrentDomainFacade $goPayFacade
    ) {
        $this->em = $em;
        $this->orderFacade = $orderFacade;
        $this->orderMailFacade = $orderMailFacade;
        $this->goPayFacade = $goPayFacade;
    }

    public function run(): void
    {
        $now = new DateTime();
        $twentyOneDaysAgo = $now->sub(DateInterval::createFromDateString('21 days'));
        $orders = $this->orderFacade->getAllUnpaidGoPayOrders($twentyOneDaysAgo);

        $this->logger->debug('Downloading status updates for ' . count($orders) . ' orders.');

        foreach ($orders as $order) {
            $this->logger->debug('Downloading GoPay status for order with ID "' . $order->getId() . '".');

            $oldOrderGoPayStatus = $order->getGoPayStatus();

            try {
                $goPayStatusResponse = $this->goPayFacade->getPaymentStatusResponse($order);
            } catch (GoPayPaymentDownloadException $e) {
                $this->logger->addError($e);

                continue;
            }

            $this->logger->info($goPayStatusResponse);

            if (array_key_exists('state', $goPayStatusResponse->json)) {
                $this->orderFacade->setGoPayStatusAndFik($order, $goPayStatusResponse);
            }

            if ($oldOrderGoPayStatus !== $order->getGoPayStatus()) {
                $this->logger->info('Order with id "' . $order->getId() . '" changed GoPay status from "' . $oldOrderGoPayStatus . '" to "' . $order->getGoPayStatus() . '".');
            }

            $this->logger->info('Order with id "' . $order->getId() . '" now has GoPay status: "' . $order->getGoPayStatus() . '".');

            if ($oldOrderGoPayStatus !== $order->getGoPayStatus() && $order->getGoPayStatus() === PaymentStatus::PAID) {
                if ($order->getStatus()->getMailTemplateName() !== null) {
                    $this->logger->info('Sending order e-mail.');
                    $this->orderMailFacade->sendEmail($order);
                }
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
