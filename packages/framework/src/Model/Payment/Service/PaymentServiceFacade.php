<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Service;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotEnabledOnDomainException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Service\Exception\PaymentServiceFacadeNotRegisteredException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Exception\PaymentTransactionHasNoAssignedPayment;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\PaymentTransactionRefundFailedException;

class PaymentServiceFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceInterface[]
     */
    protected array $paymentServices;

    public function __construct(
        protected readonly PaymentTransactionFacade $paymentTransactionFacade,
        protected readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        GoPayFacade $goPayFacade,
        protected readonly LoggerInterface $logger,
        protected readonly PaymentSetupCreationDataFactory $paymentSetupCreationDataFactory,
        protected readonly OrderPaidStatusFacade $orderPaidStatusFacade,
    ) {
        $this->paymentServices = [];
        $this->paymentServices[PaymentTypeEnum::TYPE_GOPAY] = $goPayFacade;
    }

    protected function getPaymentServiceFacadeByPaymentType(string $paymentType): PaymentServiceInterface
    {
        if (array_key_exists($paymentType, $this->paymentServices)) {
            return $this->paymentServices[$paymentType];
        }

        throw new PaymentServiceFacadeNotRegisteredException($paymentType);
    }

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
            $this->logger->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }

        return $paymentSetupCreationData;
    }

    public function updatePaymentTransactionsByOrder(Order $order): bool
    {
        $updated = false;

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

            try {
                $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPaymentThrowExceptionIfNull()->getType());
                $update = $paymentServiceFacade->updateTransaction($paymentTransactionData);

                if ($update) {
                    $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
                    $updated = true;
                }
            } catch (PaymentServiceFacadeNotRegisteredException|GoPayNotConfiguredException|GoPayNotEnabledOnDomainException|PaymentTransactionHasNoAssignedPayment $exception) {
                $this->logger->error(
                    $exception->getMessage(),
                    ['exception' => $exception],
                );
            }
        }

        $this->orderPaidStatusFacade->refreshOrderPaidStatusByPaymentTransactions($order);

        return $updated;
    }

    public function refundTransaction(PaymentTransaction $paymentTransaction, Money $refundAmount): bool
    {
        $order = $paymentTransaction->getOrder();
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);

        try {
            $paymentServiceFacade = $this->getPaymentServiceFacadeByPaymentType($paymentTransaction->getPaymentThrowExceptionIfNull()->getType());
            $refundFailed = false;

            try {
                $update = $paymentServiceFacade->refundTransaction($paymentTransactionData, $refundAmount);
            } catch (GoPayPaymentDownloadException $exception) {
                $this->logger->error(
                    'GoPay API return error.',
                    [
                        'exception' => $exception,
                        'paymentTransactionId' => $paymentTransaction->getId(),
                        'externalPaymentIdentifier' => $paymentTransaction->getExternalPaymentIdentifier(),
                    ],
                );
                $refundFailed = true;
                $update = false;
            }

            try {
                $update = $update || $paymentServiceFacade->updateTransaction($paymentTransactionData);
            } catch (GoPayPaymentDownloadException $exception) {
                $this->logger->error(
                    'GoPay API return error while updating refunded transaction.',
                    [
                        'exception' => $exception,
                        'paymentTransactionId' => $paymentTransaction->getId(),
                        'externalPaymentIdentifier' => $paymentTransaction->getExternalPaymentIdentifier(),
                    ],
                );
            }

            if ($update) {
                $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
                $this->orderPaidStatusFacade->refreshOrderPaidStatusByPaymentTransactions($order);
            }

            if ($refundFailed) {
                throw new PaymentTransactionRefundFailedException();
            }

            return $update;
        } catch (PaymentServiceFacadeNotRegisteredException|GoPayNotConfiguredException|GoPayNotEnabledOnDomainException|PaymentTransactionHasNoAssignedPayment $exception) {
            $this->logger->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }

        return false;
    }
}
