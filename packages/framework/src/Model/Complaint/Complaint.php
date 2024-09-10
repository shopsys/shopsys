<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="complaints")
 * @ORM\Entity
 */
class Complaint
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
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
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order")
     * @ORM\JoinColumn(nullable=false, name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(nullable=true, name="customer_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $customerUser;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryFirstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
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
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $status;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem",
     *     mappedBy="complaint",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    protected $items;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     */
    public function __construct(ComplaintData $complaintData, array $complaintItems)
    {
        $this->createdAt = new DateTime();
        $this->setItems($complaintItems);

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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
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
        return $this->items->toArray();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     */
    protected function setData(ComplaintData $complaintData): void
    {
        $this->uuid = $complaintData->uuid ?? Uuid::uuid4()->toString();
        $this->number = $complaintData->number;
        $this->order = $complaintData->order;
        $this->customerUser = $complaintData->customerUser;
        $this->deliveryFirstName = $complaintData->deliveryFirstName;
        $this->deliveryLastName = $complaintData->deliveryLastName;
        $this->deliveryCompanyName = $complaintData->deliveryCompanyName;
        $this->deliveryTelephone = $complaintData->deliveryTelephone;
        $this->deliveryStreet = $complaintData->deliveryStreet;
        $this->deliveryCity = $complaintData->deliveryCity;
        $this->deliveryPostcode = $complaintData->deliveryPostcode;
        $this->deliveryCountry = $complaintData->deliveryCountry;
        $this->status = $complaintData->status;
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
}
