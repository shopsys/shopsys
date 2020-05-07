<?php

declare(strict_types = 1);

namespace App\Model\GoPay;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class GoPayOnCurrentDomainFacade
{
    /**
     * @var \App\Model\GoPay\GoPayOrderMapper
     */
    private $goPayOrderMapper;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\GoPay\GoPayClientFactory
     */
    private $goPayClientFactory;

    /**
     * @param \App\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \App\Model\GoPay\GoPayOrderMapper $goPayOrderMapper
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        GoPayClientFactory $goPayClientFactory,
        GoPayOrderMapper $goPayOrderMapper,
        Domain $domain
    ) {
        $this->goPayOrderMapper = $goPayOrderMapper;
        $this->domain = $domain;
        $this->goPayClientFactory = $goPayClientFactory;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string|null $goPayBankSwift
     * @return array
     */
    public function sendPaymentToGoPay(Order $order, ?string $goPayBankSwift): array
    {
        $goPayPaymentData = $this->goPayOrderMapper->createGoPayPaymentData($order, $goPayBankSwift);
        $goPayClient = $this->goPayClientFactory->createByLocale($this->domain->getLocale());
        $response = $goPayClient->sendPaymentToGoPay($goPayPaymentData);

        if ($response->hasSucceed()) {
            return [
                'gatewayUrl' => $response->json['gw_url'],
                'embedJs' => $goPayClient->urlToEmbedJs(),
                'goPayId' => $response->json['id'],
            ];
        }

        throw new \App\Model\GoPay\Exception\GoPaySendPaymentException();
    }

    /**
     * @param \App\Model\GoPay\GoPayTransaction[] $goPayTransactions
     * @param int $domainId
     * @return \App\Model\GoPay\GoPayResponseData[]
     */
    public function getPaymentStatusesResponseDataByGoPayTransactionAndDomainId(array $goPayTransactions, int $domainId): array
    {
        $responses = [];
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $goPayClient = $this->goPayClientFactory->createByLocale($domainConfig->getLocale());

        foreach ($goPayTransactions as $goPayTransaction) {
            $responses[] = new GoPayResponseData(
                $goPayClient->getStatus($goPayTransaction->getGoPayId()),
                $goPayTransaction
            );
        }

        return $responses;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return bool
     */
    public function isOrderGoPayUnpaid(Order $order): bool
    {
        return $order->getPayment()->isGoPay() && $order->isGoPayPaid() === false;
    }
}
