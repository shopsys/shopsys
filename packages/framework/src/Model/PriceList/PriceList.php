<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="price_lists")
 * @ORM\Entity
 */
class PriceList
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $lastUpdate;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $validFrom;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $validTo;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice", mappedBy="priceList", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $productsWithPrices;

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    public function __construct(PriceListData $priceListData)
    {
        $this->productsWithPrices = new ArrayCollection();
        $this->setData($priceListData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    public function edit(PriceListData $priceListData): void
    {
        $this->setData($priceListData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    protected function setData(PriceListData $priceListData): void
    {
        $this->name = $priceListData->name;
        $this->domainId = $priceListData->domainId;
        $this->validFrom = $priceListData->validFrom;
        $this->validTo = $priceListData->validTo;
        $this->lastUpdate = new DateTimeImmutable();

        // products with prices are not set here, they are set in the PriceListFacade::refreshProductWithPrices method
        $this->productsWithPrices->clear();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice[]
     */
    public function getProductsWithPrices()
    {
        return $this->productsWithPrices->getValues();
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice $productWithPrice
     */
    public function addProductWithPrice(ProductWithPrice $productWithPrice): void
    {
        $this->productsWithPrices->add($productWithPrice);
        $productWithPrice->setPriceList($this);
    }
}
