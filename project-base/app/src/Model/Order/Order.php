<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Order\Mail\OrderMail;
use App\Model\Payment\Transaction\PaymentTransaction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \App\Model\Order\Item\OrderItem[]|\Doctrine\Common\Collections\Collection $items
 * @property \App\Model\Transport\Transport $transport
 * @property \App\Model\Payment\Payment $payment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Order\Item\OrderItem getOrderPayment()
 * @method \App\Model\Order\Item\OrderItem getOrderTransport()
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUser()
 * @method \App\Model\Order\Item\OrderItem[] getItems()
 * @method \App\Model\Order\Item\OrderItem[] getItemsWithoutTransportAndPayment()
 * @method \App\Model\Order\Item\OrderItem getItemById(int $orderItemId)
 * @method \App\Model\Order\Item\OrderItem[] getProductItems()
 * @method \App\Model\Administrator\Administrator|null getCreatedAsAdministrator()
 * @method editOrderTransport(\App\Model\Order\OrderData $orderData)
 * @method editOrderPayment(\App\Model\Order\OrderData $orderData)
 * @method setDeliveryAddress(\App\Model\Order\OrderData $orderData)
 * @method addItem(\App\Model\Order\Item\OrderItem $item)
 * @method removeItem(\App\Model\Order\Item\OrderItem $item)
 * @method fillCommonFields(\App\Model\Order\OrderData $orderData)
 * @property \App\Model\Order\Status\OrderStatus $status
 * @method setStatus(\App\Model\Order\Status\OrderStatus $status)
 * @method \App\Model\Order\Status\OrderStatus getStatus()
 * @method \App\Model\Transport\Transport getTransport()
 * @method \App\Model\Order\Item\OrderItem[] getTransportAndPaymentItems()
 * @method \Shopsys\FrameworkBundle\Model\Order\OrderEditResult edit(\App\Model\Order\OrderData $orderData)
 */
class Order extends BaseOrder
{
    public const MAX_TRANSACTION_COUNT = 2;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     * @phpstan-ignore-next-line Overridden property type
     */
    protected $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     * @phpstan-ignore-next-line Overridden property type
     */
    protected $lastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     * @phpstan-ignore-next-line Overridden property type
     */
    protected $deliveryFirstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     * @phpstan-ignore-next-line Overridden property type
     */
    protected $deliveryLastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $gtmCoupon;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected ?string $trackingNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $pickupPlaceIdentifier;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Payment\Transaction\PaymentTransaction>
     * @ORM\OneToMany(targetEntity="App\Model\Payment\Transaction\PaymentTransaction", mappedBy="order", cascade={"persist"})
     */
    private Collection $paymentTransactions;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private ?string $goPayBankSwift;

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(
        BaseOrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser = null,
    ) {
        parent::__construct($orderData, $orderNumber, $urlHash, $customerUser);

        if ($orderData->isCompanyCustomer === true) {
            $this->companyName = $orderData->companyName;
            $this->companyNumber = $orderData->companyNumber;
            $this->companyTaxNumber = $orderData->companyTaxNumber;
        } else {
            $this->companyName = null;
            $this->companyNumber = null;
            $this->companyTaxNumber = null;
        }

        $this->firstName = $orderData->firstName;
        $this->lastName = $orderData->lastName;
        $this->gtmCoupon = $orderData->gtmCoupon;
        $this->trackingNumber = $orderData->trackingNumber;
        $this->pickupPlaceIdentifier = $orderData->pickupPlaceIdentifier;
        $this->paymentTransactions = new ArrayCollection();
        $this->goPayBankSwift = $orderData->goPayBankSwift;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     */
    protected function editData(BaseOrderData $orderData): void
    {
        parent::editData($orderData);

        $this->gtmCoupon = $orderData->gtmCoupon;
        $this->trackingNumber = $orderData->trackingNumber;
    }

    /**
     * @return \App\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getGoPayTransactions(): array
    {
        $paymentTransactions = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()->isGoPay()) {
                $paymentTransactions[] = $paymentTransaction;
            }
        }

        return $paymentTransactions;
    }

    /**
     * @return string[]
     */
    public function getGoPayTransactionStatusesIndexedByGoPayId(): array
    {
        $returnArray = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()->isGoPay()) {
                $returnArray[$paymentTransaction->getExternalPaymentIdentifier()] = $paymentTransaction->getExternalPaymentStatus();
            }
        }

        return $returnArray;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        foreach ($this->paymentTransactions as $paymentTransaction) {
            if ($paymentTransaction->isPaid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \App\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getPaymentTransactions(): array
    {
        return $this->paymentTransactions->getValues();
    }

    /**
     * @return bool
     */
    public function isMaxTransactionCountReached(): bool
    {
        return $this->paymentTransactions->count() >= self::MAX_TRANSACTION_COUNT;
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     */
    public function addPaymentTransaction(PaymentTransaction $paymentTransaction): void
    {
        $this->paymentTransactions->add($paymentTransaction);
    }

    /**
     * @return string|null
     */
    public function getGtmCoupon(): ?string
    {
        return $this->gtmCoupon;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string|null
     */
    public function getTrackingUrl(): ?string
    {
        $trackingUrl = $this->transport->getTrackingUrl();
        $trackingNumber = $this->getTrackingNumber();

        if ($trackingUrl === null || $trackingNumber === null) {
            return null;
        }

        return strtr($trackingUrl, [
            OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => $trackingNumber,
        ]);
    }

    /**
     * @return string|null
     */
    public function getPickupPlaceIdentifier(): ?string
    {
        return $this->pickupPlaceIdentifier;
    }

    /**
     * @return string|null
     */
    public function getGoPayBankSwift(): ?string
    {
        return $this->goPayBankSwift;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function setCustomerUser(?CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
    }
}
