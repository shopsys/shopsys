<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Service;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotEnabledOnDomainException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Service\Exception\PaymentServiceFacadeNotRegisteredException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Exception\PaymentTransactionHasNoAssignedPayment;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentServiceFacade
{
    use FlashMessageTrait;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceInterface[]
     */
    protected array $paymentServices;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \Shopsys\FrameworkBundle\Model\GoPay\GoPayFacade $goPayFacade
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationDataFactory $paymentSetupCreationDataFactory
     */
    public function __construct(
        protected readonly PaymentTransactionFacade $paymentTransactionFacade,
        protected readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        GoPayFacade $goPayFacade,
        protected readonly LoggerInterface $logger,
        protected readonly ContainerInterface $container,
        protected readonly PaymentSetupCreationDataFactory $paymentSetupCreationDataFactory,
    ) {
        $this->paymentServices = [];
        $this->paymentServices[Payment::TYPE_GOPAY] = $goPayFacade;
    }

    /**
     * @param string $paymentType
     * @return \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceInterface
     */
    protected function getPaymentServiceFacadeByPaymentType(string $paymentType): PaymentServiceInterface
    {
        if (array_key_exists($paymentType, $this->paymentServices)) {
            return $this->paymentServices[$paymentType];
        }

        throw new PaymentServiceFacadeNotRegisteredException($paymentType);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData
     */
    public function payOrder(Order $order): PaymentSetupCreationData
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();

        $paymentSetupCreationData = $this->paymentSetupCreationDataFactory->createInstance();

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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function updatePaymentTransactionsByOrder(Order $order): void
    {
        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

            try {
                $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPaymentThrowExceptionIfNull()->getType());
                $update = $paymentServiceFacade->updateTransaction($paymentTransactionData);

                if ($update) {
                    $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
                }
            } catch (PaymentServiceFacadeNotRegisteredException|GoPayNotConfiguredException|GoPayNotEnabledOnDomainException|PaymentTransactionHasNoAssignedPayment $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $refundAmount
     */
    public function refundTransaction(PaymentTransaction $paymentTransaction, Money $refundAmount): void
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

        try {
            $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPaymentThrowExceptionIfNull()->getType());

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
        } catch (PaymentServiceFacadeNotRegisteredException|PaymentTransactionHasNoAssignedPayment $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
