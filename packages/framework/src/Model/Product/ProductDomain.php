<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

/**
 * @ORM\Table(
 *     name="product_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_domain", columns={"product_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class ProductDomain
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $vat;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp1;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp2;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp3;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp4;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $shortDescriptionUsp5;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag>
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinTable(name="product_domain_flags")
     */
    protected $flags;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $saleExclusion;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $domainHidden;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     */
    public function __construct(Product $product, $domainId)
    {
        $this->product = $product;
        $this->domainId = $domainId;
        $this->flags = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1()
    {
        return $this->seoH1;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle($seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription($seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @param string $seoH1
     */
    public function setSeoH1($seoH1): void
    {
        $this->seoH1 = $seoH1;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription($shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp1()
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
    public function getShortDescriptionUsp2()
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
    public function getShortDescriptionUsp3()
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
    public function getShortDescriptionUsp4()
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
    public function getShortDescriptionUsp5()
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlags()
    {
        return $this->flags->getValues();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flags
     */
    public function setFlags(array $flags): void
    {
        $this->flags->clear();

        foreach ($flags as $flag) {
            $this->flags->add($flag);
        }
    }

    /**
     * @return int
     */
    public function getOrderingPriority()
    {
        return $this->orderingPriority;
    }

    /**
     * @param int $orderingPriority
     */
    public function setOrderingPriority(int $orderingPriority): void
    {
        $this->orderingPriority = $orderingPriority;
    }

    /**
     * @return bool
     */
    public function getSaleExclusion()
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
     * @param bool $domainHidden
     */
    public function setDomainHidden(bool $domainHidden): void
    {
        $this->domainHidden = $domainHidden;
    }

    /**
     * @return bool
     */
    public function isDomainHidden()
    {
        return $this->domainHidden;
    }
}
