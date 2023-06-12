<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(
 *     name="zbozi_product_domains"
 * )
 * @ORM\Entity
 */
class ZboziProductDomain
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected Product $product;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $show;

    /**
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected ?Money $cpc = null;

    /**
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected ?Money $cpcSearch = null;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData $zboziProductDomainData
     */
    public function __construct(ZboziProductDomainData $zboziProductDomainData)
    {
        $this->setData($zboziProductDomainData);
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData $zboziProductDomainData
     */
    public function edit(ZboziProductDomainData $zboziProductDomainData)
    {
        $this->setData($zboziProductDomainData);
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData $zboziProductDomainData
     */
    protected function setData(ZboziProductDomainData $zboziProductDomainData): void
    {
        $this->product = $zboziProductDomainData->product;
        $this->show = $zboziProductDomainData->show;
        $this->cpc = $zboziProductDomainData->cpc;
        $this->cpcSearch = $zboziProductDomainData->cpcSearch;
        $this->domainId = $zboziProductDomainData->domainId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return bool
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getCpc(): ?Money
    {
        return $this->cpc;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getCpcSearch(): ?Money
    {
        return $this->cpcSearch;
    }
}
