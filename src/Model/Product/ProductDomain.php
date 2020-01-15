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
 *
 * @ORM\Entity
 * @property \App\Model\Product\Product $product
 * @method __construct(\App\Model\Product\Product $product, int $domainId)
 */
class ProductDomain extends BaseProductDomain
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp1;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp2;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp3;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp4;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp5;

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp1(): ?string
    {
        return $this->shortDescriptionUsp1;
    }

    /**
     * @param string|null $shortDescriptionUsp1
     */
    public function setShortDescriptionUsp1(?string $shortDescriptionUsp1): void
    {
        $this->shortDescriptionUsp1 = $shortDescriptionUsp1;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp2(): ?string
    {
        return $this->shortDescriptionUsp2;
    }

    /**
     * @param string|null $shortDescriptionUsp2
     */
    public function setShortDescriptionUsp2(?string $shortDescriptionUsp2): void
    {
        $this->shortDescriptionUsp2 = $shortDescriptionUsp2;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp3(): ?string
    {
        return $this->shortDescriptionUsp3;
    }

    /**
     * @param string|null $shortDescriptionUsp3
     */
    public function setShortDescriptionUsp3(?string $shortDescriptionUsp3): void
    {
        $this->shortDescriptionUsp3 = $shortDescriptionUsp3;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp4(): ?string
    {
        return $this->shortDescriptionUsp4;
    }

    /**
     * @param string|null $shortDescriptionUsp4
     */
    public function setShortDescriptionUsp4(?string $shortDescriptionUsp4): void
    {
        $this->shortDescriptionUsp4 = $shortDescriptionUsp4;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp5(): ?string
    {
        return $this->shortDescriptionUsp5;
    }

    /**
     * @param string|null $shortDescriptionUsp5
     */
    public function setShortDescriptionUsp5(?string $shortDescriptionUsp5): void
    {
        $this->shortDescriptionUsp5 = $shortDescriptionUsp5;
    }
}
