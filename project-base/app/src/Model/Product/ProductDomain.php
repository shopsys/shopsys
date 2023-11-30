<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain as BaseProductDomain;

/**
 * @ORM\Table(
 *     name="product_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_domain", columns={"product_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @property \App\Model\Product\Product $product
 * @property \App\Model\Product\Flag\Flag[]|\Doctrine\Common\Collections\ArrayCollection $flags
 * @method \App\Model\Product\Flag\Flag[] getFlags()
 * @method setFlags(\App\Model\Product\Flag\Flag[] $flags)
 */
class ProductDomain extends BaseProductDomain
{
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $saleExclusion;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $calculatedSaleExclusion;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $domainHidden;

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     */
    public function __construct(Product $product, $domainId)
    {
        parent::__construct($product, $domainId);

        $this->calculatedSaleExclusion = true;
    }

    /**
     * @return bool
     */
    public function getSaleExclusion(): bool
    {
        return $this->saleExclusion;
    }

    /**
     * @param bool $saleExclusion
     */
    public function setSaleExclusion(bool $saleExclusion): void
    {
        $this->saleExclusion = $saleExclusion;
    }

    /**
     * @return bool
     */
    public function getCalculatedSaleExclusion(): bool
    {
        return $this->calculatedSaleExclusion;
    }

    /**
     * @return bool
     */
    public function isDomainHidden(): bool
    {
        return $this->domainHidden;
    }

    /**
     * @param bool $domainHidden
     */
    public function setDomainHidden(bool $domainHidden): void
    {
        $this->domainHidden = $domainHidden;
    }
}
