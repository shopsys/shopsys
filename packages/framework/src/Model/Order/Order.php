<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\ExcludeLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
class Order
{
    public const int MAX_TRANSACTION_COUNT = 2;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, unique=true, nullable=false)
     */
    protected $number;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(nullable=true, name="customer_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $customerUser;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $orderPaymentStatusPageValidFrom;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Order\Item\OrderItem",
     *     mappedBy="order",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $items;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $status;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $totalPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $totalPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    #[ExcludeLog]
    protected $totalProductPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    #[ExcludeLog]
    protected $totalProductPriceWithVat;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=30)
     */
    protected $telephone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $street;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=30)
     */
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    protected $country;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deliveryAddressSameAsBillingAddress;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryFirstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryLastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryCompanyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $deliveryTelephone;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryStreet;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryCity;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30)
     */
    protected $deliveryPostcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="delivery_country_id", referencedColumnName="id", nullable=true)
     */
    protected $deliveryCountry;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected $urlHash;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=true, name="administrator_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdAsAdministrator;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $createdAsAdministratorName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $origin;

    /**
     * @var string|null
     * @ORM\Column(type="guid", nullable=true)
     */
    protected $orderPaymentStatusPageValidityHash;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction", mappedBy="order", cascade={"persist"})
     */
    protected $paymentTransactions;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $goPayBankSwift;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $heurekaAgreement;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser = null,
    ) {
        $this->fillCommonFields($orderData);

        $this->transport = $orderData->transport;
        $this->payment = $orderData->payment;

        $this->items = new ArrayCollection();

        $this->number = $orderNumber;

        $this->customerUser = $customerUser;
        $this->deleted = false;

        if ($orderData->createdAt === null) {
            $this->createdAt = new DateTime();
        } else {
            $this->createdAt = $orderData->createdAt;
        }
        $this->domainId = $orderData->domainId;
        $this->urlHash = $urlHash;
        $this->currency = $orderData->currency;
        $this->createdAsAdministrator = $orderData->createdAsAdministrator;
        $this->createdAsAdministratorName = $orderData->createdAsAdministratorName;
        $this->origin = $orderData->origin;
        $this->uuid = $orderData->uuid ?: Uuid::uuid4()->toString();
        $this->setTotalPrice(new OrderTotalPrice(Money::zero(), Money::zero(), Money::zero(), Money::zero()));
        $this->orderPaymentStatusPageValidityHash = Uuid::uuid4()->toString();
        $this->paymentTransactions = new ArrayCollection();
        $this->goPayBankSwift = $orderData->goPayBankSwift;
    }

    /**
     * @return string|null
     */
    public function getGoPayBankSwift()
    {
        return $this->goPayBankSwift;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getGoPayTransactions(): array
    {
        $paymentTransactions = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()?->isGoPay()) {
                $paymentTransactions[] = $paymentTransaction;
            }
        }

        return $paymentTransactions;
    }

    /**
     * @return bool
     */
    public function isMaxTransactionCountReached(): bool
    {
        return $this->paymentTransactions->count() >= static::MAX_TRANSACTION_COUNT;
    }

    /**
     * @return string[]
     */
    public function getGoPayTransactionStatusesIndexedByGoPayId(): array
    {
        $returnArray = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()?->isGoPay()) {
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
     * @return int
     */
    public function getPaymentTransactionsCount(): int
    {
        return $this->paymentTransactions->count();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     */
    public function addPaymentTransaction(PaymentTransaction $paymentTransaction): void
    {
        $this->paymentTransactions->add($paymentTransaction);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getPaymentTransactions()
    {
        return $this->paymentTransactions->getValues();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editData(OrderData $orderData): void
    {
        $this->fillCommonFields($orderData);

        $this->editOrderTransport($orderData);
        $this->editOrderPayment($orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillCommonFields(OrderData $orderData): void
    {
        $this->firstName = $orderData->firstName;
        $this->lastName = $orderData->lastName;
        $this->email = $orderData->email;
        $this->telephone = $orderData->telephone;
        $this->street = $orderData->street;
        $this->city = $orderData->city;
        $this->postcode = $orderData->postcode;
        $this->country = $orderData->country;
        $this->note = $orderData->note;

        $this->setCompanyInfo(
            $orderData->companyName,
            $orderData->companyNumber,
            $orderData->companyTaxNumber,
        );

        $this->status = $orderData->status;
        $this->heurekaAgreement = $orderData->heurekaAgreement;

        $this->setDeliveryAddress($orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editOrderTransport(OrderData $orderData): void
    {
        $orderTransportData = $orderData->orderTransport;
        $this->transport = $orderTransportData->transport;
        $this->getTransportItem()->edit($orderTransportData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editOrderPayment(OrderData $orderData): void
    {
        $orderPaymentData = $orderData->orderPayment;
        $this->payment = $orderPaymentData->payment;
        $this->getPaymentItem()->edit($orderPaymentData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setDeliveryAddress(OrderData $orderData): void
    {
        $this->deliveryAddressSameAsBillingAddress = $orderData->deliveryAddressSameAsBillingAddress;

        if ($orderData->deliveryAddressSameAsBillingAddress) {
            $this->deliveryFirstName = $orderData->firstName;
            $this->deliveryLastName = $orderData->lastName;
            $this->deliveryCompanyName = $orderData->companyName;
            $this->deliveryTelephone = $orderData->telephone;
            $this->deliveryStreet = $orderData->street;
            $this->deliveryCity = $orderData->city;
            $this->deliveryPostcode = $orderData->postcode;
            $this->deliveryCountry = $orderData->country;
        } else {
            $this->deliveryFirstName = $orderData->deliveryFirstName;
            $this->deliveryLastName = $orderData->deliveryLastName;
            $this->deliveryCompanyName = $orderData->deliveryCompanyName;
            $this->deliveryTelephone = $orderData->deliveryTelephone;
            $this->deliveryStreet = $orderData->deliveryStreet;
            $this->deliveryCity = $orderData->deliveryCity;
            $this->deliveryPostcode = $orderData->deliveryPostcode;
            $this->deliveryCountry = $orderData->deliveryCountry;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $item
     */
    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $item
     */
    public function removeItem(OrderItem $item): void
    {
        if ($item->isTypeTransport()) {
            $this->transport = null;
        }

        if ($item->isTypePayment()) {
            $this->payment = null;
        }
        $this->items->removeElement($item);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @param string|null $companyName
     * @param string|null $companyNumber
     * @param string|null $companyTaxNumber
     */
    public function setCompanyInfo($companyName = null, $companyNumber = null, $companyTaxNumber = null): void
    {
        $this->companyName = $companyName;
        $this->companyNumber = $companyNumber;
        $this->companyTaxNumber = $companyTaxNumber;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     * @deprecated use getPaymentItem() instead
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     * @deprecated use getTransportItem() instead
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalPriceWithVat()
    {
        return $this->totalPriceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalPriceWithoutVat()
    {
        return $this->totalPriceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalVatAmount(): Money
    {
        return $this->totalPriceWithVat->subtract($this->totalPriceWithoutVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderTotalPrice $orderTotalPrice
     */
    public function setTotalPrice(OrderTotalPrice $orderTotalPrice): void
    {
        $this->totalPriceWithVat = $orderTotalPrice->getPriceWithVat();
        $this->totalPriceWithoutVat = $orderTotalPrice->getPriceWithoutVat();
        $this->totalProductPriceWithVat = $orderTotalPrice->getProductPriceWithVat();
        $this->totalProductPriceWithoutVat = $orderTotalPrice->getProductPriceWithoutVat();
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function markAsDeleted()
    {
        $this->deleted = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    #[EntityLogIdentify]
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getOrderPaymentStatusPageValidFrom()
    {
        return $this->orderPaymentStatusPageValidFrom;
    }

    public function setOrderPaymentStatusPageValidFromNow(): void
    {
        $this->orderPaymentStatusPageValidFrom = new DateTime();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsByType(string $type): array
    {
        return array_filter(
            $this->items->getValues(),
            fn (OrderItem $item) => $item->isType($type),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getProductItems(): array
    {
        return $this->getItemsByType(OrderItem::TYPE_PRODUCT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function getTransportItem(): OrderItem
    {
        $transports = $this->getItemsByType(OrderItem::TYPE_TRANSPORT);

        if (count($transports) === 0) {
            throw new OrderItemNotFoundException('Order item `transport` not found.');
        }

        return reset($transports);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function getPaymentItem(): OrderItem
    {
        $payments = $this->getItemsByType(OrderItem::TYPE_PAYMENT);

        if (count($payments) === 0) {
            throw new OrderItemNotFoundException('Order item `payment` not found.');
        }

        return reset($payments);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getDiscountItems(): array
    {
        return $this->getItemsByType(OrderItem::TYPE_DISCOUNT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getRoundingItems(): array
    {
        return $this->getItemsByType(OrderItem::TYPE_ROUNDING);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsWithoutTransportAndPayment()
    {
        $itemsWithoutTransportAndPayment = [];

        foreach ($this->getItems() as $orderItem) {
            if (!($orderItem->isTypeTransport() || $orderItem->isTypePayment())) {
                $itemsWithoutTransportAndPayment[] = $orderItem;
            }
        }

        return $itemsWithoutTransportAndPayment;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return string
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * @return string
     */
    public function getCompanyTaxNumber()
    {
        return $this->companyTaxNumber;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return bool
     */
    public function isDeliveryAddressSameAsBillingAddress()
    {
        return $this->deliveryAddressSameAsBillingAddress;
    }

    /**
     * @return string
     */
    public function getDeliveryFirstName()
    {
        return $this->deliveryFirstName;
    }

    /**
     * @return string
     */
    public function getDeliveryLastName()
    {
        return $this->deliveryLastName;
    }

    /**
     * @return string
     */
    public function getDeliveryCompanyName()
    {
        return $this->deliveryCompanyName;
    }

    /**
     * @return string
     */
    public function getDeliveryTelephone()
    {
        return $this->deliveryTelephone;
    }

    /**
     * @return string
     */
    public function getDeliveryStreet()
    {
        return $this->deliveryStreet;
    }

    /**
     * @return string
     */
    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    /**
     * @return string
     */
    public function getDeliveryPostcode()
    {
        return $this->deliveryPostcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getDeliveryCountry()
    {
        return $this->deliveryCountry;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getUrlHash()
    {
        return $this->urlHash;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function getCreatedAsAdministrator()
    {
        return $this->createdAsAdministrator;
    }

    /**
     * @return string|null
     */
    public function getCreatedAsAdministratorName()
    {
        return $this->createdAsAdministratorName;
    }

    /**
     * @return string|null
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status->getType() === OrderStatus::TYPE_CANCELED;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderEditResult
     */
    public function edit(OrderData $orderData): OrderEditResult
    {
        $statusChanged = $this->getStatus() !== $orderData->status;
        $this->editData($orderData);

        return new OrderEditResult($statusChanged);
    }

    /**
     * @return string|null
     */
    public function getOrderPaymentStatusPageValidityHash()
    {
        return $this->orderPaymentStatusPageValidityHash;
    }

    public function setOrderPaymentStatusPageValidityHashToNull(): void
    {
        $this->orderPaymentStatusPageValidityHash = null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalProductsPrice(): Price
    {
        return new Price($this->totalProductPriceWithoutVat, $this->totalProductPriceWithVat);
    }

    /**
     * @return bool
     */
    public function isHeurekaAgreement()
    {
        return $this->heurekaAgreement;
    }
}
