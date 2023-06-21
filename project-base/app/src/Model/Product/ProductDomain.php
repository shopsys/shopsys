<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
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
 */
class ProductDomain extends BaseProductDomain
{
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
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $assemblyInstructionCode;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $productTypePlanCode;

    /**
     * @var \App\Model\Product\Flag\Flag[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Flag\Flag")
     * @ORM\JoinTable(name="product_domain_flags")
     */
    protected $flags;

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
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainOrderingPriority;

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     */
    public function __construct(Product $product, $domainId)
    {
        parent::__construct($product, $domainId);

        $this->flags = new ArrayCollection();
        $this->calculatedSaleExclusion = true;
    }

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

    /**
     * @return string|null
     */
    public function getAssemblyInstructionCode(): ?string
    {
        return $this->assemblyInstructionCode;
    }

    /**
     * @param string|null $assemblyInstructionCode
     */
    public function setAssemblyInstructionCode(?string $assemblyInstructionCode): void
    {
        $this->assemblyInstructionCode = $assemblyInstructionCode;
    }

    /**
     * @return string|null
     */
    public function getProductTypePlanCode(): ?string
    {
        return $this->productTypePlanCode;
    }

    /**
     * @param string|null $productTypePlanCode
     */
    public function setProductTypePlanCode(?string $productTypePlanCode): void
    {
        $this->productTypePlanCode = $productTypePlanCode;
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags->getValues();
    }

    /**
     * @param \App\Model\Product\Flag\Flag[] $flags
     */
    public function setFlags(array $flags): void
    {
        $this->flags->clear();

        foreach ($flags as $flag) {
            $this->flags->add($flag);
        }
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

    /**
     * @return int
     */
    public function getDomainOrderingPriority(): int
    {
        return $this->domainOrderingPriority;
    }

    /**
     * @param int $domainOrderingPriority
     */
    public function setDomainOrderingPriority(int $domainOrderingPriority): void
    {
        $this->domainOrderingPriority = $domainOrderingPriority;
    }
}
