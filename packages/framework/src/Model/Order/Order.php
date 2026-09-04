<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\ExcludeLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
#[ORM\Table(name: 'orders')]
#[ORM\Entity]
class Order implements DomainSeparatedEntityInterface
{
    public const int MAX_TRANSACTION_COUNT = 2;

    protected const array SORTED_TYPES = [
        OrderItemTypeEnum::TYPE_PRODUCT,
        OrderItemTypeEnum::TYPE_PRODUCT_GIFT,
        OrderItemTypeEnum::TYPE_DISCOUNT,
        OrderItemTypeEnum::TYPE_PAYMENT,
        OrderItemTypeEnum::TYPE_TRANSPORT,
        OrderItemTypeEnum::TYPE_ROUNDING,
    ];

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30, unique: true, nullable: false)]
    protected $number;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $deliveredAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $items;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: OrderStatus::class)]
    protected $status;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $totalPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $totalPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ExcludeLog]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $totalProductPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ExcludeLog]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $totalProductPriceWithVat;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $firstName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $lastName;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $telephonePrefix;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $telephonePrefixCountryCode;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30)]
    protected $telephoneNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $companyName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyTaxNumber;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $street;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $city;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30)]
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected $country;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $deliveryAddressSameAsBillingAddress;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $deliveryFirstName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $deliveryLastName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $deliveryCompanyName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $deliveryTelephonePrefix;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $deliveryTelephonePrefixCountryCode;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $deliveryTelephoneNumber;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $deliveryStreet;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $deliveryCity;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30)]
    protected $deliveryPostcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'delivery_country_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected $deliveryCountry;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $note;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $deleted;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    protected $urlHash;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    protected $createdAsAdministrator;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $createdAsAdministratorName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    protected $origin;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction>
     */
    #[ORM\OneToMany(targetEntity: PaymentTransaction::class, mappedBy: 'order', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $paymentTransactions;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher>
     */
    #[ORM\OneToMany(targetEntity: GiftVoucher::class, mappedBy: 'redeemedOnOrder')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $redeemedGiftVouchers;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $goPayBankSwift;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $paid;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $paidAt;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $heurekaAgreement;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $pickupPlaceIdentifier;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $trackingNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    protected $customer;

    /**
     * @var bool|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $freeTransportAndPaymentApplied;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 3)]
    protected $currencyCode;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 15)]
    protected $currencyRoundingType;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $currencyRoundingPlacesPriceWithoutVat;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $currencyMinFractionDigits;

    public function __construct(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser = null,
    ) {
        $this->fillCommonFields($orderData);

        $this->items = new ArrayCollection();

        $this->number = $orderNumber;

        $this->setCustomerUser($customerUser);
        $this->deleted = false;
        $this->paid = false;

        $this->createdAt = $orderData->createdAt;
        $this->domainId = $orderData->domainId;
        $this->urlHash = $urlHash;
        $this->createdAsAdministrator = $orderData->createdAsAdministrator;
        $this->createdAsAdministratorName = $orderData->createdAsAdministratorName;
        $this->origin = $orderData->origin;
        $this->uuid = $orderData->uuid ?: Uuid::uuid4()->toString();
        $this->setTotalPrices(Price::zero(), Price::zero());
        $this->paymentTransactions = new ArrayCollection();
        $this->redeemedGiftVouchers = new ArrayCollection();
        $this->goPayBankSwift = $orderData->goPayBankSwift;
        $this->pickupPlaceIdentifier = $orderData->pickupPlaceIdentifier;

        $this->currencyCode = $orderData->currencyCode;
        $this->currencyRoundingType = $orderData->currencyRoundingType;
        $this->currencyRoundingPlacesPriceWithoutVat = $orderData->currencyRoundingPlacesPriceWithoutVat;
        $this->currencyMinFractionDigits = $orderData->currencyMinFractionDigits;
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

    public function getLastGoPayTransaction(): ?PaymentTransaction
    {
        $lastTransaction = $this->paymentTransactions->last();

        if ($lastTransaction && $lastTransaction->getPayment()?->isGoPay()) {
            return $lastTransaction;
        }

        return null;
    }

    public function getLastTransaction(): ?PaymentTransaction
    {
        return $this->paymentTransactions->last() ?: null;
    }

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

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function hasPaidPaymentTransaction(): bool
    {
        foreach ($this->paymentTransactions as $paymentTransaction) {
            if ($paymentTransaction->isPaid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getPaidAt()
    {
        return $this->paidAt;
    }

    public function hasElectronicGiftVoucherProductItems(): bool
    {
        foreach ($this->getProductItems() as $orderItem) {
            $product = $orderItem->getProduct();

            if ($product !== null && $product->isElectronicGiftVoucher()) {
                return true;
            }
        }

        return false;
    }

    public function hasOnlyElectronicGiftVoucherProductItems(): bool
    {
        $productItems = $this->getProductItems();

        if ($productItems === []) {
            return false;
        }

        foreach ($productItems as $orderItem) {
            $product = $orderItem->getProduct();

            if ($product === null || !$product->isElectronicGiftVoucher()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function getRedeemedGiftVouchers()
    {
        return $this->redeemedGiftVouchers->getValues();
    }

    public function addRedeemedGiftVoucher(GiftVoucher $giftVoucher): void
    {
        if (!$this->redeemedGiftVouchers->contains($giftVoucher)) {
            $this->redeemedGiftVouchers->add($giftVoucher);
        }
    }

    public function getRemainingAmountToPay(): Money
    {
        $remainingAmountToPay = $this->getTotalPriceWithVat();

        foreach ($this->getRedeemedGiftVouchers() as $giftVoucher) {
            $remainingAmountToPay = $remainingAmountToPay->subtract($giftVoucher->getValueWithVat());
        }

        if ($remainingAmountToPay->isNegative()) {
            return Money::zero();
        }

        return $remainingAmountToPay;
    }

    public function markAsPaid(DateTimeImmutable $paidAt): void
    {
        if ($this->paid) {
            return;
        }

        $this->paid = true;
        $this->paidAt = $paidAt;
    }

    public function hasPaymentInProcess(): bool
    {
        foreach ($this->paymentTransactions as $paymentTransaction) {
            if ($paymentTransaction->hasPaymentInProcess()) {
                return true;
            }
        }

        return false;
    }

    public function getPaymentTransactionsCount(): int
    {
        return $this->paymentTransactions->count();
    }

    public function getLastExternalPaymentUrl(): ?string
    {
        return $this->getLastGoPayTransaction()?->getExternalPaymentUrl();
    }

    /**
     * @return string|null
     */
    public function getLastExternalPaymentStatus()
    {
        return $this->getLastGoPayTransaction()?->getExternalPaymentStatus();
    }

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
     * @return bool
     */
    public function isFreeTransportAndPaymentApplied()
    {
        return $this->freeTransportAndPaymentApplied;
    }

    protected function editData(OrderData $orderData): void
    {
        $this->fillCommonFields($orderData);

        $this->editOrderTransport($orderData);
        $this->editOrderPayment($orderData);
    }

    protected function fillCommonFields(OrderData $orderData): void
    {
        $this->firstName = $orderData->firstName;
        $this->lastName = $orderData->lastName;
        $this->email = $orderData->email;
        $this->setTelephoneData($orderData->telephone);
        $this->street = $orderData->street;
        $this->city = $orderData->city;
        $this->postcode = $orderData->postcode;
        $this->country = $orderData->country;
        $this->note = $orderData->note;
        $this->trackingNumber = $orderData->trackingNumber;

        if ($orderData->isCompanyCustomer === true) {
            $this->setCompanyInfo(
                $orderData->companyName,
                $orderData->companyNumber,
                $orderData->companyTaxNumber,
            );
        } else {
            $this->setCompanyInfo();
        }

        $this->status = $orderData->status;
        $this->heurekaAgreement = $orderData->heurekaAgreement;
        $this->deliveredAt = $orderData->deliveredAt;

        $this->setDeliveryAddress($orderData);

        $this->promoCode = $orderData->promoCode;
        $this->freeTransportAndPaymentApplied = $orderData->freeTransportAndPaymentApplied;
    }

    protected function editOrderTransport(OrderData $orderData): void
    {
        $orderTransportData = $orderData->orderTransport;
        $this->getTransportItem()->edit($orderTransportData);
    }

    protected function editOrderPayment(OrderData $orderData): void
    {
        $orderPaymentData = $orderData->orderPayment;
        $this->getPaymentItem()->edit($orderPaymentData);
    }

    protected function setDeliveryAddress(OrderData $orderData): void
    {
        $this->deliveryAddressSameAsBillingAddress = $orderData->deliveryAddressSameAsBillingAddress;

        if ($orderData->deliveryAddressSameAsBillingAddress) {
            $this->deliveryFirstName = $orderData->firstName;
            $this->deliveryLastName = $orderData->lastName;
            $this->deliveryCompanyName = $orderData->companyName;
            $this->setDeliveryTelephoneData($orderData->telephone);
            $this->deliveryStreet = $orderData->street;
            $this->deliveryCity = $orderData->city;
            $this->deliveryPostcode = $orderData->postcode;
            $this->deliveryCountry = $orderData->country;
        } else {
            $this->deliveryFirstName = $orderData->deliveryFirstName;
            $this->deliveryLastName = $orderData->deliveryLastName;
            $this->deliveryCompanyName = $orderData->deliveryCompanyName;
            $this->setDeliveryTelephoneData($orderData->deliveryTelephone);
            $this->deliveryStreet = $orderData->deliveryStreet;
            $this->deliveryCity = $orderData->deliveryCity;
            $this->deliveryPostcode = $orderData->deliveryPostcode;
            $this->deliveryCountry = $orderData->deliveryCountry;
        }
    }

    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    public function removeItem(OrderItem $item): void
    {
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
    public function setDomainId($domainId): void
    {
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getPayment()
    {
        $payment = $this->getPaymentItem()->getPayment();

        if ($payment === null) {
            throw new OrderItemNotFoundException('Order item `payment` not found.');
        }

        return $payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getTransport()
    {
        $transport = $this->getTransportItem()->getTransport();

        if ($transport === null) {
            throw new OrderItemNotFoundException('Order item `transport` not found.');
        }

        return $transport;
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice()
    {
        return new Price($this->totalPriceWithoutVat, $this->totalPriceWithVat);
    }

    public function getTotalVatAmount(): Money
    {
        return $this->totalPriceWithVat->subtract($this->totalPriceWithoutVat);
    }

    /**
     * @param array<string> $excludedTypes
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceExcludingItemTypes(array $excludedTypes)
    {
        $withoutVat = $this->totalPriceWithoutVat;
        $withVat = $this->totalPriceWithVat;

        foreach ($this->getItems() as $item) {
            if (in_array($item->getType(), $excludedTypes, true)) {
                $withoutVat = $withoutVat->subtract($item->getUnitPriceWithoutVat()->multiply($item->getQuantity()));
                $withVat = $withVat->subtract($item->getUnitPriceWithVat()->multiply($item->getQuantity()));
            }
        }

        return new Price($withoutVat, $withVat);
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getCurrencyRoundingType()
    {
        return $this->currencyRoundingType;
    }

    /**
     * @return int
     */
    public function getCurrencyRoundingPlacesPriceWithoutVat()
    {
        return $this->currencyRoundingPlacesPriceWithoutVat;
    }

    /**
     * @return int
     */
    public function getCurrencyMinFractionDigits()
    {
        return $this->currencyMinFractionDigits;
    }

    public function setTotalPrices(PriceInterface $orderTotalPrice, PriceInterface $productsTotalPrice): void
    {
        $this->totalPriceWithVat = $orderTotalPrice->getPriceWithVat();
        $this->totalPriceWithoutVat = $orderTotalPrice->getPriceWithoutVat();
        $this->totalProductPriceWithVat = $productsTotalPrice->getPriceWithVat();
        $this->totalProductPriceWithoutVat = $productsTotalPrice->getPriceWithoutVat();
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function markAsDeleted(): void
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
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeliveredAt()
    {
        return $this->deliveredAt;
    }

    /**
     * @param \DateTimeImmutable $deliveredAt
     */
    public function setDeliveredAt($deliveredAt): void
    {
        $this->deliveredAt = $deliveredAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsSortedWithRelatedItems(): array
    {
        $itemsSortedWithRelatedItems = [];

        $items = clone $this->items;

        foreach (static::SORTED_TYPES as $orderItemType) {
            foreach ($this->getItemsByType($orderItemType) as $orderItem) {
                if (!$items->contains($orderItem)) {
                    continue;
                }

                $itemsSortedWithRelatedItems[] = $orderItem;
                $items->removeElement($orderItem);

                foreach ($orderItem->getRelatedItems() as $relatedOrderItem) {
                    if (!$items->contains($relatedOrderItem)) {
                        continue;
                    }

                    $itemsSortedWithRelatedItems[] = $relatedOrderItem;
                    $items->removeElement($relatedOrderItem);
                }
            }
        }

        return $itemsSortedWithRelatedItems;
    }

    /**
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
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT);
    }

    public function getTransportItem(): OrderItem
    {
        $transports = $this->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT);

        if (count($transports) === 0) {
            throw new OrderItemNotFoundException('Order item `transport` not found.');
        }

        return array_first($transports);
    }

    public function getPaymentItem(): OrderItem
    {
        $payments = $this->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        if (count($payments) === 0) {
            throw new OrderItemNotFoundException('Order item `payment` not found.');
        }

        return array_first($payments);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getDiscountItems(): array
    {
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getRoundingItems(): array
    {
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING);
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

    public function getTelephone(): string
    {
        return $this->getTelephoneData()->toPhoneNumber();
    }

    public function getTelephoneData(): PhoneData
    {
        return new PhoneData(
            $this->telephonePrefixCountryCode,
            $this->telephonePrefix,
            $this->telephoneNumber,
        );
    }

    public function setTelephoneData(PhoneData $phoneData): void
    {
        $this->telephonePrefix = $phoneData->prefix;
        $this->telephonePrefixCountryCode = $phoneData->countryCode;
        $this->telephoneNumber = $phoneData->number;
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

    public function getDeliveryTelephone(): ?string
    {
        return $this->getDeliveryTelephoneData()?->toPhoneNumber();
    }

    public function getDeliveryTelephoneData(): ?PhoneData
    {
        if ($this->deliveryTelephoneNumber === null) {
            return null;
        }

        return new PhoneData(
            $this->deliveryTelephonePrefixCountryCode,
            $this->deliveryTelephonePrefix,
            $this->deliveryTelephoneNumber,
        );
    }

    public function setDeliveryTelephoneData(?PhoneData $phoneData): void
    {
        if ($phoneData === null) {
            $this->deliveryTelephonePrefix = null;
            $this->deliveryTelephonePrefixCountryCode = null;
            $this->deliveryTelephoneNumber = null;

            return;
        }

        $this->deliveryTelephonePrefix = $phoneData->prefix;
        $this->deliveryTelephonePrefixCountryCode = $phoneData->countryCode;
        $this->deliveryTelephoneNumber = $phoneData->number;
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
    #[Override]
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

    public function isCancelled(): bool
    {
        return $this->status->getType() === OrderStatusTypeEnum::TYPE_CANCELED;
    }

    public function edit(OrderData $orderData): OrderEditResult
    {
        $statusChanged = $this->getStatus() !== $orderData->status;
        $this->editData($orderData);

        return new OrderEditResult($statusChanged);
    }

    public function getTotalProductsPrice(): PriceInterface
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

    /**
     * @return string|null
     */
    public function getPickupPlaceIdentifier()
    {
        return $this->pickupPlaceIdentifier;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber($trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getTrackingUrl(): ?string
    {
        $trackingUrl = $this->getTransport()->getTrackingUrl();
        $trackingNumber = $this->getTrackingNumber();

        if ($trackingUrl === null || $trackingNumber === null) {
            return null;
        }

        return strtr($trackingUrl, [
            OrderMail::VARIABLE_TRANSPORT_TRACKING_NUMBER => $trackingNumber,
        ]);
    }

    /**
     * @return string|null
     */
    public function getPromoCode()
    {
        return $this->promoCode;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function setCustomerUser($customerUser): void
    {
        $this->customerUser = $customerUser;
        $this->customer = $customerUser?->getCustomer();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function isCompanyCustomer(): bool
    {
        return $this->getCompanyName() !== null && $this->getCompanyNumber() !== null;
    }

    public function getTotalWeight(): int
    {
        $totalWeight = 0;

        foreach ($this->getProductItems() as $item) {
            try {
                $product = $item->getProduct();
                $totalWeight += $product->getWeight() * $item->getQuantity();
            } catch (ProductNotFoundException) {
                continue;
            }
        }

        return $totalWeight;
    }

    public function hasExternalPayment(): bool
    {
        return !in_array($this->getPayment()->getType(), PaymentTypeEnum::INTERNAL_PAYMENTS, true);
    }
}
