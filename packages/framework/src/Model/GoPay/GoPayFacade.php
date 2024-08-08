<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use DateTime;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPaySendPaymentException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceInterface;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData;

class GoPayFacade implements PaymentServiceInterface
{
    protected const string GOPAY_RESULT_FAILED = 'FAILED';

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \Shopsys\FrameworkBundle\Model\GoPay\GoPayOrderMapper $goPayOrderMapper
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\GoPay\GoPayRepository $goPayRepository
     */
    public function __construct(
        protected readonly GoPayClientFactory $goPayClientFactory,
        protected readonly GoPayOrderMapper $goPayOrderMapper,
        protected readonly Domain $domain,
        protected readonly GoPayRepository $goPayRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string|null $goPayBankSwift
     * @return array
     */
    public function sendPaymentToGoPay(Order $order, ?string $goPayBankSwift): array
    {
        $goPayPaymentData = $this->goPayOrderMapper->createGoPayPaymentData($order, $goPayBankSwift);
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);
        $response = $goPayClient->sendPaymentToGoPay($goPayPaymentData);

        if ($response->hasSucceed()) {
            return [
                'gatewayUrl' => $response->json['gw_url'],
                'embedJs' => $goPayClient->urlToEmbedJs(),
                'goPayId' => $response->json['id'],
                'state' => $response->json['state'],
            ];
        }

        throw new GoPaySendPaymentException('Creating gopay payment failed. (Details: ' . implode(' - ', $response->json['errors'][0] ?? ['unknown error']) . ')');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData $paymentSetupCreationData
     */
    public function createTransaction(
        PaymentTransactionData $paymentTransactionData,
        PaymentSetupCreationData $paymentSetupCreationData,
    ): void {
        $goPayCreatePaymentSetup = $this->sendPaymentToGoPay(
            $paymentTransactionData->order,
            $paymentTransactionData->order->getGoPayBankSwift(),
        );
        $paymentTransactionData->externalPaymentIdentifier = (string)$goPayCreatePaymentSetup['goPayId'];
        $paymentTransactionData->externalPaymentStatus = (string)$goPayCreatePaymentSetup['state'];

        $paymentSetupCreationData->setGoPayCreatePaymentSetup($goPayCreatePaymentSetup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return bool
     */
    public function updateTransaction(PaymentTransactionData $paymentTransactionData): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($paymentTransactionData->order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);
        $goPayStatusResponse = $goPayClient->getStatus($paymentTransactionData->externalPaymentIdentifier);

        if (array_key_exists('state', (array)$goPayStatusResponse->json)) {
            $paymentTransactionData->externalPaymentStatus = (string)$goPayStatusResponse->json['state'];

            if ($paymentTransactionData->externalPaymentStatus === PaymentStatus::REFUNDED) {
                $paymentTransactionData->refundedAmount = $paymentTransactionData->paidAmount;
            }

            return true;
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $refundAmount
     * @return bool
     */
    public function refundTransaction(PaymentTransactionData $paymentTransactionData, Money $refundAmount): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($paymentTransactionData->order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);
        $refundResponse = $goPayClient->refundTransaction(
            $paymentTransactionData->externalPaymentIdentifier,
            $this->goPayOrderMapper->formatPriceForGoPay($refundAmount),
        );

        if (array_key_exists('result', (array)$refundResponse->json) && $refundResponse->json['result'] !== static::GOPAY_RESULT_FAILED) {
            $paymentTransactionData->refundedAmount = $paymentTransactionData->refundedAmount->add($refundAmount);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime $fromDate
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTime $fromDate): array
    {
        return $this->goPayRepository->getAllUnpaidGoPayOrders($fromDate);
    }
}
