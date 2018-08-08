<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="google_product_domains"
 * )
 * @ORM\Entity
 */
class GoogleProductDomain
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
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    public function __construct(GoogleProductDomainData $googleProductDomainData)
    {
        $this->product = $googleProductDomainData->product;
        $this->show = $googleProductDomainData->show;
        $this->domainId = $googleProductDomainData->domainId;
    }

    public function edit(GoogleProductDomainData $googleProductDomainData): void
    {
        $this->product = $googleProductDomainData->product;
        $this->show = $googleProductDomainData->show;
        $this->domainId = $googleProductDomainData->domainId;
    }

    public function getShow(): bool
    {
        return $this->show;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
