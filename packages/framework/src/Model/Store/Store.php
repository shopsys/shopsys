<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours;

#[ORM\Table(name: 'stores')]
#[ORM\Entity]
class Store implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\Stock|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'stores', cascade: ['persist'])]
    protected $stock;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $isDefault;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    protected $externalId;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $street;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $city;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 30)]
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected $country;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours>
     */
    #[ORM\OneToMany(targetEntity: OpeningHours::class, mappedBy: 'store', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $openingHours;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $email;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $phone;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $directions;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $specialMessage;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 20, scale: 10, nullable: true)]
    protected $latitude;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 20, scale: 10, nullable: true)]
    protected $longitude;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortablePosition]
    protected $position;

    /**
     * @var int|null
     */
    protected $distance = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     */
    public function __construct(StoreData $storeData)
    {
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->uuid = $storeData->uuid ?: Uuid::uuid4()->toString();
        $this->openingHours = new ArrayCollection();
        $this->domainId = $storeData->domainId;
        $this->setData($storeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     */
    public function edit(StoreData $storeData)
    {
        $this->setData($storeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[] $openingHours
     */
    public function setOpeningHours($openingHours): void
    {
        $this->openingHours = new ArrayCollection($openingHours);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     */
    public function setData(StoreData $storeData): void
    {
        $this->isDefault = $storeData->isDefault;
        $this->name = $storeData->name;
        $this->stock = $storeData->stock;
        $this->description = $storeData->description;
        $this->externalId = $storeData->externalId;
        $this->street = $storeData->street;
        $this->city = $storeData->city;
        $this->postcode = $storeData->postcode;
        $this->country = $storeData->country;
        $this->specialMessage = $storeData->specialMessage;
        $this->latitude = $storeData->latitude;
        $this->longitude = $storeData->longitude;
        $this->email = $storeData->email;
        $this->phone = $storeData->phone;
        $this->directions = $storeData->directions;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock|null
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
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
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[]
     */
    public function getOpeningHours()
    {
        return $this->openingHours->getValues();
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getDirections()
    {
        return $this->directions;
    }

    /**
     * @return string|null
     */
    public function getSpecialMessage()
    {
        return $this->specialMessage;
    }

    /**
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function setDefault(): void
    {
        $this->isDefault = true;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return int|null
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     */
    public function setDistance($distance): void
    {
        $this->distance = $distance;
    }
}
