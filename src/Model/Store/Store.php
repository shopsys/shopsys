<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Store\Exception\StoreDomainNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="stores")
 * @ORM\Entity
 */
class Store implements OrderableEntityInterface
{
    private const GEDMO_SORTABLE_LAST_POSITION = 1;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \App\Model\Store\StoreDomain[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="App\Model\Store\StoreDomain", mappedBy="store", cascade={"persist"})
     */
    protected Collection $domains;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isDefault;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected string $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected ?string $externalId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $address;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $openingHours;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $contactInfo;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $specialMessage;

    /**
     * @var string|null
     * @ORM\Column(type="decimal", precision=16, scale=13, nullable=true)
     */
    protected ?string $locationLatitude;

    /**
     * @var string|null
     * @ORM\Column(type="decimal", precision=16, scale=13, nullable=true)
     */
    protected ?string $locationLongitude;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected int $position;

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function __construct(StoreData $storeData)
    {
        $this->domains = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->createDomains($storeData);
        $this->setData($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function edit(StoreData $storeData)
    {
        $this->setDomains($storeData);
        $this->setData($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function setData(StoreData $storeData): void
    {
        $this->isDefault = $storeData->isDefault;
        $this->name = $storeData->name;
        $this->description = $storeData->description;
        $this->externalId = $storeData->externalId;
        $this->address = $storeData->address;
        $this->openingHours = $storeData->openingHours;
        $this->contactInfo = $storeData->contactInfo;
        $this->specialMessage = $storeData->specialMessage;
        $this->locationLatitude = $storeData->locationLatitude;
        $this->locationLongitude = $storeData->locationLongitude;
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    protected function createDomains(StoreData $storeData): void
    {
        $domainIds = array_keys($storeData->isEnabledOnDomains);

        foreach ($domainIds as $domainId) {
            $storeDomain = new StoreDomain($this, $domainId);
            $this->domains->add($storeDomain);
        }

        $this->setDomains($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    protected function setDomains(StoreData $storeData): void
    {
        foreach ($this->domains as $storeDomain) {
            $domainId = $storeDomain->getDomainId();
            $storeDomain->setEnabled($storeData->isEnabledOnDomains[$domainId]);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    /**
     * @return string|null
     */
    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }

    /**
     * @return string|null
     */
    public function getSpecialMessage(): ?string
    {
        return $this->specialMessage;
    }

    /**
     * @return string|null
     */
    public function getLocationLatitude(): ?string
    {
        return $this->locationLatitude;
    }

    /**
     * @return string|null
     */
    public function getLocationLongitude(): ?string
    {
        return $this->locationLongitude;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabled(int $domainId): bool
    {
        return $this->getStoreDomain($domainId)->isEnabled();
    }

    /**
     * @param int $domainId
     * @return \App\Model\Store\StoreDomain
     */
    protected function getStoreDomain(int $domainId): StoreDomain
    {
        foreach ($this->domains as $storeDomain) {
            if ($storeDomain->getDomainId() === $domainId) {
                return $storeDomain;
            }
        }

        throw new StoreDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function setDefault(): void
    {
        $this->isDefault = true;
    }
}
