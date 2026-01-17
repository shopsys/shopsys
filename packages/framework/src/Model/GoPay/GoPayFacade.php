<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use DateTimeImmutable;
use GoPay\Definition\Response\PaymentStatus;
use Nette\Utils\Arrays;
use Override;
use Psr\Log\LoggerInterface;
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

    public function __construct(
        protected readonly GoPayClientFactory $goPayClientFactory,
        protected readonly GoPayOrderMapper $goPayOrderMapper,
        protected readonly Domain $domain,
        protected readonly GoPayRepository $goPayRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

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
                'subState' => Arrays::get($response->json, 'sub_state', null),
            ];
        }

        $this->logger->error('Creating gopay payment failed.', [
            'GoPay response' => $response,
            'GoPay payment data' => $goPayPaymentData,
        ]);

        throw new GoPaySendPaymentException();
    }

    #[Override]
    public function createTransaction(
        PaymentTransactionData $paymentTransactionData,
        PaymentSetupCreationData $paymentSetupCreationData,
    ): void {
        $goPayCreatePaymentSetup = $this->sendPaymentToGoPay(
            $paymentTransactionData->order,
            $paymentTransactionData->order->getGoPayBankSwift(),
        );

        $paymentTransactionData->externalPaymentMethod = $paymentTransactionData->payment->getGoPayPaymentMethodByDomainId($paymentTransactionData->order->getDomainId())?->getIdentifier();
        $paymentTransactionData->externalPaymentIdentifier = (string)$goPayCreatePaymentSetup['goPayId'];
        $paymentTransactionData->externalPaymentStatus = (string)$goPayCreatePaymentSetup['state'];
        $paymentTransactionData->externalPaymentSubStatus = (string)$goPayCreatePaymentSetup['subState'];
        $paymentTransactionData->externalPaymentUrl = (string)$goPayCreatePaymentSetup['gatewayUrl'];

        $paymentSetupCreationData->setGoPayCreatePaymentSetup($goPayCreatePaymentSetup);
    }

    #[Override]
    public function updateTransaction(PaymentTransactionData $paymentTransactionData): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($paymentTransactionData->order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);
        $goPayStatusResponse = $goPayClient->getStatus($paymentTransactionData->externalPaymentIdentifier);

        if (array_key_exists('state', (array)$goPayStatusResponse->json)) {
            $paymentTransactionData->externalPaymentStatus = (string)$goPayStatusResponse->json['state'];
            $paymentTransactionData->externalPaymentSubStatus = Arrays::get($goPayStatusResponse->json, 'sub_state', null);
            $paymentTransactionData->externalPaymentUrl = Arrays::get($goPayStatusResponse->json, 'gw_url', null);
            $paymentTransactionData->externalPaymentMethod = Arrays::get($goPayStatusResponse->json, 'payment_instrument', null);

            if ($paymentTransactionData->externalPaymentStatus === PaymentStatus::REFUNDED) {
                $paymentTransactionData->refundedAmount = $paymentTransactionData->paidAmount;
            }

            return true;
        }

        return false;
    }

    #[Override]
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTimeImmutable $fromDate): array
    {
        return $this->goPayRepository->getAllUnpaidGoPayOrders($fromDate);
    }
}
