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
 * @property \App\Model\Order\Status\OrderStatus|null $status
 */
class OrderData extends BaseOrderData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\Store|null
     */
    public $personalPickupStore;

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
    public $gtmCoupon;

    /**
     * @var string|null
     */
    public ?string $trackingNumber;

    /**
     * @var string|null
     */
    public ?string $pickupPlaceIdentifier;

    /**
     * @var bool|null
     */
    public ?bool $newsletterSubscription = null;

    public function __construct()
    {
        parent::__construct();

        $this->isCompanyCustomer = false;
        $this->trackingNumber = null;
        $this->pickupPlaceIdentifier = null;
    }
}
