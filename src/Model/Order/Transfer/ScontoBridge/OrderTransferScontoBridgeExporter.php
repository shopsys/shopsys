<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeExporter;
use App\Model\Order\Order;

class OrderTransferScontoBridgeExporter extends AbstractScontoBridgeExporter
{
    private const URI_ERP_ORDER = '/api/services/app/ErpOrder/SaveErpOrder';

    /**
     * @var OrderTransferScontoBridgeMapper
     */
    private OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper;

    /**
     * @var ScontoBridgeClient
     */
    private ScontoBridgeClient $scontoBridgeClient;

    /**
     * @param OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper
     * @param ScontoBridgeClient $scontoBridgeClient
     */
    public function __construct(
        OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper,
        ScontoBridgeClient $scontoBridgeClient
    ) {
        $this->orderTransferScontoBridgeMapper = $orderTransferScontoBridgeMapper;
        $this->scontoBridgeClient = $scontoBridgeClient;
    }

    /**
     * @param Order $order
     */
    public function exportOrderToScontoBridge(Order $order): void
    {
        $erpOrder = $this->orderTransferScontoBridgeMapper->mapOrderToScontoBridgeOrderData($order);
        $uri = self::URI_ERP_ORDER;
        $response = $this->scontoBridgeClient->post($uri, $erpOrder);
        if ($this->transferFailed($response)) {
            throw $this->createTransferException($response);
        }
    }
}
