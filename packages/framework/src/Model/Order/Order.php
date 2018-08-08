<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderPayment;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderTransport;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
class Order
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, unique=true, nullable=false)
     */
    protected $number;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User")
     * @ORM\JoinColumn(nullable=true, name="customer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $customer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Order\Item\OrderItem", mappedBy="order", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $items;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $totalPriceWithVat;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $totalPriceWithoutVat;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $totalProductPriceWithVat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30)
     */
    protected $telephone;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $city;

    /**
     * @var string
     *
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
     *
     * @ORM\Column(type="boolean")
     */
    protected $deliveryAddressSameAsBillingAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryFirstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryLastName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryCompanyName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $deliveryTelephone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryStreet;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $deliveryCity;

    /**
     * @var string|null
     *
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
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var int
     *
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected $urlHash;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=true, name="administrator_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdAsAdministrator;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $createdAsAdministratorName;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User|null $user
     */
    public function __construct(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        User $user = null
    ) {
        $this->transport = $orderData->transport;
        $this->payment = $orderData->payment;
        $this->firstName = $orderData->firstName;
        $this->lastName = $orderData->lastName;
        $this->email = $orderData->email;
        $this->telephone = $orderData->telephone;
        $this->street = $orderData->street;
        $this->city = $orderData->city;
        $this->postcode = $orderData->postcode;
        $this->country = $orderData->country;
        $this->note = $orderData->note;
        $this->items = new ArrayCollection();
        $this->setCompanyInfo(
            $orderData->companyName,
            $orderData->companyNumber,
            $orderData->companyTaxNumber
        );
        $this->setDeliveryAddress($orderData);
        $this->number = $orderNumber;
        $this->status = $orderData->status;
        $this->customer = $user;
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
    }

    public function edit(OrderData $orderData): void
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
            $orderData->companyTaxNumber
        );
        $this->setDeliveryAddress($orderData);
        $this->status = $orderData->status;

        $this->editOrderTransport($orderData);
        $this->editOrderPayment($orderData);
    }

    protected function editOrderTransport(OrderData $orderData): void
    {
        $orderTransportData = $orderData->orderTransport;
        $this->transport = $orderTransportData->transport;
        $this->getOrderTransport()->edit($orderTransportData);
    }

    protected function editOrderPayment(OrderData $orderData): void
    {
        $orderPaymentData = $orderData->orderPayment;
        $this->payment = $orderPaymentData->payment;
        $this->getOrderPayment()->edit($orderPaymentData);
    }

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

    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    public function removeItem(OrderItem $item): void
    {
        if ($item instanceof OrderTransport) {
            $this->transport = null;
        }
        if ($item instanceof OrderPayment) {
            $this->payment = null;
        }
        $this->items->removeElement($item);
    }

    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @param string|null $companyName
     * @param string|null $companyNumber
     * @param string|null $companyTaxNumber
     */
    public function setCompanyInfo(?string $companyName = null, ?string $companyNumber = null, ?string $companyTaxNumber = null): void
    {
        $this->companyName = $companyName;
        $this->companyNumber = $companyNumber;
        $this->companyTaxNumber = $companyTaxNumber;
    }
    
    public function setDomainId(int $domainId): void
    {
        $this->domainId = $domainId;
    }

    public function getPayment(): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        return $this->payment;
    }

    public function getPaymentName(): string
    {
        return $this->getOrderPayment()->getName();
    }

    public function getOrderPayment(): \Shopsys\FrameworkBundle\Model\Order\Item\OrderPayment
    {
        foreach ($this->items as $item) {
            if ($item instanceof OrderPayment) {
                return $item;
            }
        }
    }

    public function getTransport(): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        return $this->transport;
    }

    public function getTransportName(): string
    {
        return $this->getOrderTransport()->getName();
    }

    public function getOrderTransport(): \Shopsys\FrameworkBundle\Model\Order\Item\OrderTransport
    {
        foreach ($this->items as $item) {
            if ($item instanceof OrderTransport) {
                return $item;
            }
        }
    }

    public function getStatus(): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        return $this->status;
    }

    public function getTotalPriceWithVat(): string
    {
        return $this->totalPriceWithVat;
    }

    public function getTotalPriceWithoutVat(): string
    {
        return $this->totalPriceWithoutVat;
    }

    public function getTotalVatAmount(): string
    {
        return $this->totalPriceWithVat - $this->totalPriceWithoutVat;
    }

    public function getTotalProductPriceWithVat(): string
    {
        return $this->totalProductPriceWithVat;
    }

    public function getCurrency(): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->currency;
    }

    public function setTotalPrice(OrderTotalPrice $orderTotalPrice): void
    {
        $this->totalPriceWithVat = $orderTotalPrice->getPriceWithVat();
        $this->totalPriceWithoutVat = $orderTotalPrice->getPriceWithoutVat();
        $this->totalProductPriceWithVat = $orderTotalPrice->getProductPriceWithVat();
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function markAsDeleted(): void
    {
        $this->deleted = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getCustomer(): ?\Shopsys\FrameworkBundle\Model\Customer\User
    {
        return $this->customer;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsWithoutTransportAndPayment(): array
    {
        $itemsWithoutTransportAndPayment = [];
        foreach ($this->getItems() as $orderItem) {
            if (!($orderItem instanceof OrderTransport || $orderItem instanceof OrderPayment)) {
                $itemsWithoutTransportAndPayment[] = $orderItem;
            }
        }

        return $itemsWithoutTransportAndPayment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    protected function getTransportAndPaymentItems(): array
    {
        $transportAndPaymentItems = [];
        foreach ($this->getItems() as $orderItem) {
            if ($orderItem instanceof OrderTransport || $orderItem instanceof OrderPayment) {
                $transportAndPaymentItems[] = $orderItem;
            }
        }

        return $transportAndPaymentItems;
    }

    public function getTransportAndPaymentPrice(): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        $transportAndPaymentItems = $this->getTransportAndPaymentItems();
        $totalPrice = new Price(0, 0);

        foreach ($transportAndPaymentItems as $item) {
            $itemPrice = new Price($item->getPriceWithoutVat(), $item->getPriceWithVat());
            $totalPrice = $totalPrice->add($itemPrice);
        }

        return $totalPrice;
    }
    
    public function getItemById(int $orderItemId): \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
    {
        foreach ($this->getItems() as $orderItem) {
            if ($orderItem->getId() === $orderItemId) {
                return $orderItem;
            }
        }
        throw new \Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException(['id' => $orderItemId]);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCompanyNumber(): string
    {
        return $this->companyNumber;
    }

    public function getCompanyTaxNumber(): string
    {
        return $this->companyTaxNumber;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function getCountry(): \Shopsys\FrameworkBundle\Model\Country\Country
    {
        return $this->country;
    }

    public function isDeliveryAddressSameAsBillingAddress(): bool
    {
        return $this->deliveryAddressSameAsBillingAddress;
    }

    public function getDeliveryFirstName(): string
    {
        return $this->deliveryFirstName;
    }

    public function getDeliveryLastName(): string
    {
        return $this->deliveryLastName;
    }

    public function getDeliveryCompanyName(): string
    {
        return $this->deliveryCompanyName;
    }

    public function getDeliveryTelephone(): string
    {
        return $this->deliveryTelephone;
    }

    public function getDeliveryStreet(): string
    {
        return $this->deliveryStreet;
    }

    public function getDeliveryCity(): string
    {
        return $this->deliveryCity;
    }

    public function getDeliveryPostcode(): string
    {
        return $this->deliveryPostcode;
    }

    public function getDeliveryCountry(): ?\Shopsys\FrameworkBundle\Model\Country\Country
    {
        return $this->deliveryCountry;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getUrlHash(): string
    {
        return $this->urlHash;
    }

    public function getProductItemsCount(): int
    {
        return count($this->getProductItems());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct[]
     */
    public function getProductItems(): array
    {
        $productItems = [];
        foreach ($this->items as $item) {
            if ($item instanceof OrderProduct) {
                $productItems[] = $item;
            }
        }

        return $productItems;
    }

    public function getCreatedAsAdministrator(): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->createdAsAdministrator;
    }

    public function getCreatedAsAdministratorName(): ?string
    {
        return $this->createdAsAdministratorName;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::TYPE_CANCELED;
    }
}
