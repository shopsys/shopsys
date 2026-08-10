<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'complaints')]
#[ORM\Entity]
class Complaint implements DomainSeparatedEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

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
    #[ORM\Column(type: 'string', length: 30, unique: true, nullable: false)]
    protected $number;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Order::class)]
    protected $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

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
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: ComplaintStatus::class)]
    protected $status;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem>
     */
    #[ORM\OneToMany(targetEntity: ComplaintItem::class, mappedBy: 'complaint', cascade: ['persist'], orphanRemoval: true)]
    protected $items;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    protected $customer;

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
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $manualDocumentNumber;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    protected $resolution;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 34, nullable: true)]
    protected $bankAccountNumber;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     */
    public function __construct(ComplaintData $complaintData, array $complaintItems)
    {
        $this->createdAt = $complaintData->createdAt;

        $this->uuid = $complaintData->uuid ?? Uuid::uuid4()->toString();
        $this->number = $complaintData->number;
        $this->domainId = $complaintData->domainId;
        $this->order = $complaintData->order;
        $this->setCustomerUser($complaintData->customerUser);

        $this->setData($complaintData);

        $this->setItems($complaintItems);
    }

    public function edit(ComplaintData $complaintData): void
    {
        $this->setData($complaintData);
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
    public function getNumber()
    {
        return $this->number;
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return string|null
     */
    public function getDeliveryFirstName()
    {
        return $this->deliveryFirstName;
    }

    /**
     * @return string|null
     */
    public function getDeliveryLastName()
    {
        return $this->deliveryLastName;
    }

    /**
     * @return string|null
     */
    public function getDeliveryCompanyName()
    {
        return $this->deliveryCompanyName;
    }

    /**
     * @return string|null
     */
    public function getDeliveryTelephone()
    {
        return $this->getDeliveryTelephoneData()?->toPhoneNumber();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData|null
     */
    public function getDeliveryTelephoneData()
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
     * @return string|null
     */
    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    /**
     * @return string|null
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
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    protected function setData(ComplaintData $complaintData): void
    {
        $this->deliveryFirstName = $complaintData->deliveryFirstName;
        $this->deliveryLastName = $complaintData->deliveryLastName;
        $this->deliveryCompanyName = $complaintData->deliveryCompanyName;
        $this->setDeliveryTelephoneData($complaintData->deliveryTelephone);
        $this->deliveryStreet = $complaintData->deliveryStreet;
        $this->deliveryCity = $complaintData->deliveryCity;
        $this->deliveryPostcode = $complaintData->deliveryPostcode;
        $this->deliveryCountry = $complaintData->deliveryCountry;
        $this->status = $complaintData->status;
        $this->email = $complaintData->email;
        $this->manualDocumentNumber = $complaintData->manualDocumentNumber;
        $this->resolution = $complaintData->resolution;
        $this->bankAccountNumber = $complaintData->bankAccountNumber;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $items
     */
    protected function setItems($items): void
    {
        $this->items = new ArrayCollection($items);

        foreach ($items as $item) {
            $item->setComplaint($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    protected function setCustomerUser($customerUser): void
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

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getManualDocumentNumber()
    {
        return $this->manualDocumentNumber;
    }

    public function getOrderNumberOrManualDocumentNumber(): string
    {
        return $this->order?->getNumber() ?? $this->manualDocumentNumber;
    }

    /**
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * @return string|null
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }
}
