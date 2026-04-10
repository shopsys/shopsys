<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'price_lists')]
#[ORM\Entity]
class PriceList
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

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
    #[ORM\Column(type: 'string', length: 100)]
    protected $name;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $lastUpdate;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $validFrom;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $validTo;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice>
     */
    #[ORM\OneToMany(targetEntity: PriceListProductPrice::class, mappedBy: 'priceList', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $priceListProductPrices;

    public function __construct(PriceListData $priceListData)
    {
        $this->priceListProductPrices = new ArrayCollection();
        $this->setData($priceListData);
    }

    public function edit(PriceListData $priceListData): void
    {
        $this->setData($priceListData);
    }

    protected function setData(PriceListData $priceListData): void
    {
        $this->name = $priceListData->name;
        $this->domainId = $priceListData->domainId;
        $this->validFrom = $priceListData->validFrom;
        $this->validTo = $priceListData->validTo;
        $this->lastUpdate = new DatePoint();

        // products with prices are not set here, they are set in the PriceListFacade::refreshPriceListProductPrices method
        $this->priceListProductPrices->clear();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice[]
     */
    public function getPriceListProductPrices()
    {
        return $this->priceListProductPrices->getValues();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function addPriceListProductPrice(PriceListProductPrice $priceListProductPrice): void
    {
        $this->priceListProductPrices->add($priceListProductPrice);
        $priceListProductPrice->setPriceList($this);
    }

    /**
     * @return int[]
     */
    public function getProductIds(): array
    {
        return array_map(
            static fn (PriceListProductPrice $priceListProductPrice) => $priceListProductPrice->getProduct()->getId(),
            $this->getPriceListProductPrices(),
        );
    }
}
