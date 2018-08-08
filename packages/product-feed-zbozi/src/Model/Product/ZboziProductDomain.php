<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="zbozi_product_domains"
 * )
 * @ORM\Entity
 */
class ZboziProductDomain
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $show;

    /**
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
     */
    protected $cpc;

    /**
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
     */
    protected $cpcSearch;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    public function __construct(ZboziProductDomainData $zboziProductDomainData)
    {
        $this->product = $zboziProductDomainData->product;
        $this->show = $zboziProductDomainData->show;
        $this->cpc = $zboziProductDomainData->cpc;
        $this->cpcSearch = $zboziProductDomainData->cpcSearch;
        $this->domainId = $zboziProductDomainData->domainId;
    }

    public function edit(ZboziProductDomainData $zboziProductDomainData)
    {
        $this->product = $zboziProductDomainData->product;
        $this->show = $zboziProductDomainData->show;
        $this->cpc = $zboziProductDomainData->cpc;
        $this->cpcSearch = $zboziProductDomainData->cpcSearch;
        $this->domainId = $zboziProductDomainData->domainId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function getShow(): bool
    {
        return $this->show;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getCpc(): ?string
    {
        return $this->cpc;
    }

    public function getCpcSearch(): ?string
    {
        return $this->cpcSearch;
    }
}
