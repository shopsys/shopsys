<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="heureka_product_domains"
 * )
 * @ORM\Entity
 */
class HeurekaProductDomain
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
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
     */
    protected $cpc;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    public function __construct(HeurekaProductDomainData $heurekaProductDomainData)
    {
        $this->product = $heurekaProductDomainData->product;
        $this->cpc = $heurekaProductDomainData->cpc;
        $this->domainId = $heurekaProductDomainData->domainId;
    }

    public function edit(HeurekaProductDomainData $heurekaProductDomainData)
    {
        $this->product = $heurekaProductDomainData->product;
        $this->cpc = $heurekaProductDomainData->cpc;
        $this->domainId = $heurekaProductDomainData->domainId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function getCpc(): ?string
    {
        return $this->cpc;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
