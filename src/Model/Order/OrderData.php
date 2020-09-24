<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Order\Item\OrderItemData[] $itemsWithoutTransportAndPayment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @property \App\Model\Order\Item\OrderItemData|null $orderPayment
 * @property \App\Model\Order\Item\OrderItemData|null $orderTransport
 * @method \App\Model\Order\Item\OrderItemData[] getNewItemsWithoutTransportAndPayment()
 */
class OrderData extends BaseOrderData
{
    /**
     * @var \App\Model\Transport\Transport[]
     */
    public $transportsByProductTypeId;

    /**
     * @var \App\Model\Stock\Stock[]
     */
    public $transportPersonalPickupStockByProductTypeId;

    /**
     * @var \App\Model\GoPay\GoPayTransaction[]
     */
    public $goPayTransactions;

    /**
     * @var string|null
     */
    public $gender;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var bool|null
     */
    public $isCompanyCustomer;

    /**
     * @var string|null
     */
    public $companyNumberWithVat;

    /**
     * @var string|null
     */
    public $gtmCoupon;

    /**
     * @var string
     */
    public $scontoBridgeStatus;

    public function __construct()
    {
        parent::__construct();

        $this->isCompanyCustomer = false;
        $this->scontoBridgeStatus = OrderScontoBridgeStatusEnum::STATUS_NEW;
    }
}
