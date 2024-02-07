<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Stock\Exception\StockDomainNotFoundException;

/**
 * @ORM\Table(name="stocks")
 * @ORM\Entity
 */
class Stock implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = 1;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Stock\StockDomain>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Stock\StockDomain", mappedBy="stock", cascade={"persist"})
     */
    protected $domains;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Store\Store>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Store\Store", mappedBy="stock", cascade={"persist"})
     */
    protected $stores;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $externalId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isDefault;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    public function __construct(StockData $stockData)
    {
        $this->domains = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->createDomains($stockData);
        $this->setData($stockData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    public function edit(StockData $stockData): void
    {
        $this->setDomains($stockData);
        $this->setData($stockData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    public function setData(StockData $stockData): void
    {
        $this->name = $stockData->name;
        $this->externalId = $stockData->externalId;
        $this->isDefault = $stockData->isDefault;
        $this->note = $stockData->note;
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
    public function getName()
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
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabled(int $domainId): bool
    {
        return $this->getStockDomain($domainId)->isEnabled();
    }

    /**
     * @return bool[]
     */
    public function getEnabledIndexedByDomainId(): array
    {
        $return = [];

        foreach ($this->domains as $domain) {
            $return[$domain->getDomainId()] = $domain->isEnabled();
        }

        return $return;
    }

    /**
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    public function setDefault(): void
    {
        $this->isDefault = true;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    protected function createDomains(StockData $stockData): void
    {
        $domainIds = array_keys($stockData->isEnabledByDomain);

        foreach ($domainIds as $domainId) {
            $stockDomain = new StockDomain($this, $domainId);
            $this->domains->add($stockDomain);
        }

        $this->setDomains($stockData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    protected function setDomains(StockData $stockData): void
    {
        foreach ($this->domains as $stockDomain) {
            $domainId = $stockDomain->getDomainId();
            $stockDomain->setEnabled($stockData->isEnabledByDomain[$domainId]);
        }
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockDomain
     */
    public function getStockDomain(int $domainId): StockDomain
    {
        foreach ($this->domains as $stockDomain) {
            if ($stockDomain->getDomainId() === $domainId) {
                return $stockDomain;
            }
        }

        throw new StockDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStores()
    {
        return $this->stores->getValues();
    }
}
