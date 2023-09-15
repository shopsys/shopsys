<?php

declare(strict_types=1);

namespace App\Model\Payment\Service;

use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\GoPayFacade;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use App\Model\Payment\Service\Exception\PaymentServiceFacadeNotRegisteredException;
use App\Model\Payment\Transaction\PaymentTransaction;
use App\Model\Payment\Transaction\PaymentTransactionDataFactory;
use App\Model\Payment\Transaction\PaymentTransactionFacade;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentServiceFacade
{
    use FlashMessageTrait;

    /**
     * @var \App\Model\Payment\Service\PaymentServiceInterface[]
     */
    private array $paymentServices;

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \App\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \App\Model\GoPay\GoPayFacade $goPayFacade
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        private PaymentTransactionFacade $paymentTransactionFacade,
        private PaymentTransactionDataFactory $paymentTransactionDataFactory,
        GoPayFacade $goPayFacade,
        private LoggerInterface $logger,
        ContainerInterface $container,
    ) {
        $this->paymentServices = [];
        $this->paymentServices[Payment::TYPE_GOPAY] = $goPayFacade;
        $this->container = $container;
    }

    /**
     * @param string $paymentType
     * @return \App\Model\Payment\Service\PaymentServiceInterface
     */
    private function getPaymentServiceFacadeByPaymentType(string $paymentType): PaymentServiceInterface
    {
        if (array_key_exists($paymentType, $this->paymentServices)) {
            return $this->paymentServices[$paymentType];
        }

        throw new PaymentServiceFacadeNotRegisteredException($paymentType);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\FrontendApi\Model\Payment\PaymentSetupCreationData
     */
    public function payOrder(Order $order): PaymentSetupCreationData
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();

        $paymentSetupCreationData = new PaymentSetupCreationData();

        try {
            $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($order->getPayment()->getType());
            $paymentServiceFacade->createTransaction($paymentTransactionData, $paymentSetupCreationData);
            $this->paymentTransactionFacade->create($paymentTransactionData);
        } catch (PaymentServiceFacadeNotRegisteredException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $paymentSetupCreationData;
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    public function updatePaymentTransactionsByOrder(Order $order): void
    {
        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

            try {
                $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPayment()->getType());
                $update = $paymentServiceFacade->updateTransaction($paymentTransactionData);

                if ($update) {
                    $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
                }
            } catch (PaymentServiceFacadeNotRegisteredException | GoPayNotConfiguredException $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $refundAmount
     */
    public function refundTransaction(PaymentTransaction $paymentTransaction, Money $refundAmount): void
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

        try {
            $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPayment()->getType());

            try {
                $update = $paymentServiceFacade->refundTransaction($paymentTransactionData, $refundAmount);
            } catch (GoPayPaymentDownloadException $exception) {
                $this->addErrorFlash(t('GoPay API return error - go to GoPay admin and find transaction %paymentId% and check if is all right.', ['%paymentId%' => $paymentTransaction->getExternalPaymentIdentifier()]));
                $this->logger->error('GoPay API return error.', [$exception]);
                $update = false;
            }

            try {
                $update = $update || $paymentServiceFacade->updateTransaction($paymentTransactionData);
            } catch (GoPayPaymentDownloadException $exception) {
                $update = $update || false; // @phpstan-ignore-line
            }

            if ($update) {
                $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
            }
        } catch (PaymentServiceFacadeNotRegisteredException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
