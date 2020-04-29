<?php

declare(strict_types = 1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use GoPay\Definition\Response\PaymentStatus;
use GoPay\Http\Response;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class GoPayOnCurrentDomainFacade
{
    /**
     * @var \App\Model\GoPay\GoPayOrderMapper
     */
    private $goPayOrderMapper;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\GoPay\GoPayClientFactory
     */
    private $goPayClientFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \App\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @param \App\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \App\Model\GoPay\GoPayOrderMapper $goPayOrderMapper
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        GoPayClientFactory $goPayClientFactory,
        GoPayOrderMapper $goPayOrderMapper,
        Domain $domain,
        LoggerInterface $logger,
        OrderFacade $orderFacade
    ) {
        $this->goPayOrderMapper = $goPayOrderMapper;
        $this->domain = $domain;
        $this->goPayClientFactory = $goPayClientFactory;
        $this->logger = $logger;
        $this->orderFacade = $orderFacade;
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

    //TODO transactions
//    /**
//     * @param \App\Model\GoPay\GoPayTransaction[] $goPayTransactions
//     * @param int $domainId
//     * @return \App\Model\GoPay\GoPayResponseData[]
//     */
//    public function getPaymentStatusesResponseDataByGoPayTransactionAndDomainId(array $goPayTransactions, int $domainId): array
//    {
//        $responses = [];
//        $domainConfig = $this->domain->getDomainConfigById($domainId);
//        $goPayClient = $this->goPayClientFactory->createByLocale($domainConfig->getLocale());
//
//        foreach ($goPayTransactions as $goPayTransaction) {
//            $responses[] = new GoPayResponseData(
//                $goPayClient->getStatus($goPayTransaction->getGoPayId()),
//                $goPayTransaction
//            );
//        }
//
//        return $responses;
//    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \GoPay\Http\Response
     */
    public function getPaymentStatusResponse(Order $order): Response
    {
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByLocale($domainConfig->getLocale());

        $response = $goPayClient->getStatus($order->getGoPayId());

        return $response;
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    public function checkOrderGoPayStatus(Order $order): void
    {
        if ($order->getGoPayStatus() === PaymentStatus::PAID) {
            return;
        }

        try {
            $goPayStatusResponse = $this->getPaymentStatusResponse($order);
            $this->orderFacade->setGoPayStatusAndFik($order, $goPayStatusResponse);
        } catch (GoPayNotConfiguredException $e) {
            $this->logger->error($e);
            throw $e;
        } catch (GoPayPaymentDownloadException $e) {
            $this->logger->error($e);
            throw $e;
        }
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return bool
     */
    public function isOrderGoPayUnpaid(Order $order): bool
    {
        return $order->getPayment()->isGoPay() && $order->isGopayPaid() === false;
    }
}
