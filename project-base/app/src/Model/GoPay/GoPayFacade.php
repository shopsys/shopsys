<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\GoPay\Exception\GoPaySendPaymentException;
use App\Model\Order\Order;
use App\Model\Payment\Service\PaymentServiceInterface;
use App\Model\Payment\Transaction\PaymentTransactionData;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;

class GoPayFacade implements PaymentServiceInterface
{
    private const GOPAY_RESULT_FAILED = 'FAILED';

    /**
     * @var \App\Model\GoPay\GoPayOrderMapper
     */
    private $goPayOrderMapper;

    /**
     * @var \App\Model\GoPay\GoPayClientFactory
     */
    private $goPayClientFactory;

    /**
     * @var \App\Model\GoPay\GoPayClient[]
     */
    private array $goPayClients;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \App\Model\GoPay\GoPayOrderMapper $goPayOrderMapper
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        GoPayClientFactory $goPayClientFactory,
        GoPayOrderMapper $goPayOrderMapper,
        Domain $domain
    ) {
        $this->goPayOrderMapper = $goPayOrderMapper;
        $this->goPayClientFactory = $goPayClientFactory;
        $this->goPayClients = [];
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @return \App\Model\GoPay\GoPayClient
     */
    private function getGoPayClientByDomainConfig(DomainConfig $domain): GoPayClient
    {
        $locale = $domain->getLocale();

        if (array_key_exists($locale, $this->goPayClients) === false) {
            $this->goPayClients[$locale] = $this->goPayClientFactory->createByLocale($locale);
        }

        return $this->goPayClients[$locale];
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string|null $goPayBankSwift
     * @return array
     */
    public function sendPaymentToGoPay(Order $order, ?string $goPayBankSwift): array
    {
        $goPayPaymentData = $this->goPayOrderMapper->createGoPayPaymentData($order, $goPayBankSwift);
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $response = $this->getGoPayClientByDomainConfig($domainConfig)->sendPaymentToGoPay($goPayPaymentData);

        if ($response->hasSucceed()) {
            return [
                'gatewayUrl' => $response->json['gw_url'],
                'embedJs' => $this->getGoPayClientByDomainConfig($domainConfig)->urlToEmbedJs(),
                'goPayId' => $response->json['id'],
                'state' => $response->json['state'],
            ];
        }

        throw new GoPaySendPaymentException('Creating gopay payment failed. (Details: ' . implode(' - ', $response->json['errors'][0] ?? ['unknown error']) . ')');
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \App\FrontendApi\Model\Payment\PaymentSetupCreationData $paymentSetupCreationData
     */
    public function createTransaction(PaymentTransactionData $paymentTransactionData, PaymentSetupCreationData $paymentSetupCreationData): void
    {
        $goPayCreatePaymentSetup = $this->sendPaymentToGoPay($paymentTransactionData->order, $paymentTransactionData->order->getGoPayBankSwift());

        $paymentTransactionData->externalPaymentIdentifier = (string)$goPayCreatePaymentSetup['goPayId'];
        $paymentTransactionData->externalPaymentStatus = (string)$goPayCreatePaymentSetup['state'];

        $paymentSetupCreationData->setGoPayCreatePaymentSetup($goPayCreatePaymentSetup);
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return bool
     */
    public function updateTransaction(PaymentTransactionData $paymentTransactionData): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($paymentTransactionData->order->getDomainId());
        $goPayStatusResponse = $this->getGoPayClientByDomainConfig($domainConfig)->getStatus($paymentTransactionData->externalPaymentIdentifier);
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
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $refundAmount
     * @return bool
     */
    public function refundTransaction(PaymentTransactionData $paymentTransactionData, Money $refundAmount): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($paymentTransactionData->order->getDomainId());
        $refundResponse = $this->getGoPayClientByDomainConfig($domainConfig)->refundTransaction($paymentTransactionData->externalPaymentIdentifier, $this->goPayOrderMapper->formatPriceForGoPay($refundAmount));
        if (array_key_exists('result', (array)$refundResponse->json) && $refundResponse->json['result'] !== self::GOPAY_RESULT_FAILED) {
            $paymentTransactionData->refundedAmount = $paymentTransactionData->refundedAmount->add($refundAmount);
            return true;
        }

        return false;
    }
}
