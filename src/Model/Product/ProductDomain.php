<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Type\ProductType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
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
 */
class ProductDomain extends BaseProductDomain
{
    public const FLAG_PRODUCT_SALE_AKENEO_CODE = 'flag__product_sale';

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
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     *
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $lowPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     *
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $highPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     *
     * @ORM\Column(type="money", precision=20, scale=6, nullable=false)
     */
    protected $sellingPriceWithVat;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $assemblyInstructionCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $productTypePlanCode;

    /**
     * @var \App\Model\Product\Type\ProductType
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Type\ProductType")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id", nullable=false)
     */
    private $productType;

    /**
     * @var \App\Model\Product\Flag\Flag[]|\Doctrine\Common\Collections\ArrayCollection
     *
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
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mountingState;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $embeddedAccessories;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $packageNotIncluded;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packagingUnit;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countPackages;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalPackageWeight;

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

        $this->flags = new ArrayCollection();
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
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getLowPriceWithVat(): ?Money
    {
        return $this->lowPriceWithVat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $lowPriceWithVat
     */
    public function setLowPriceWithVat(?Money $lowPriceWithVat): void
    {
        $this->lowPriceWithVat = $lowPriceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getHighPriceWithVat(): ?Money
    {
        return $this->highPriceWithVat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $highPriceWithVat
     */
    public function setHighPriceWithVat(?Money $highPriceWithVat): void
    {
        $this->highPriceWithVat = $highPriceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getSellingPriceWithVat(): ?Money
    {
        return $this->sellingPriceWithVat;
    }

    public function calcSellingPriceWithVat(): void
    {
        if ($this->lowPriceWithVat !== null && $this->lowPriceWithVat->getAmount() > 0) {
            $this->sellingPriceWithVat = $this->lowPriceWithVat;
        } elseif ($this->highPriceWithVat !== null && $this->highPriceWithVat->getAmount() > 0) {
            $this->sellingPriceWithVat = $this->highPriceWithVat;
        } else {
            $this->sellingPriceWithVat = Money::zero();
        }
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
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     */
    public function setProductType(ProductType $productType): void
    {
        $this->productType = $productType;
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags->toArray();
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
     * @param \App\Model\Product\Flag\Flag[] $flags
     * @return bool
     */
    public function calcSaleExclusion($flags): bool
    {
        $exclusion = false;

        foreach ($flags as $flag) {
            if ($flag->getAkeneoCode() === self::FLAG_PRODUCT_SALE_AKENEO_CODE) {
                $exclusion = true;
                break;
            }
        }

        return $exclusion;
    }

    /**
     * @return int|null
     */
    public function getCountPackages(): ?int
    {
        return $this->countPackages;
    }

    /**
     * @param int|null $countPackages
     */
    public function setCountPackages(?int $countPackages): void
    {
        $this->countPackages = $countPackages;
    }

    /**
     * @return bool|null
     */
    public function isMountingState(): ?bool
    {
        return $this->mountingState;
    }

    /**
     * @param bool $mountingState
     */
    public function setMountingState(?bool $mountingState): void
    {
        $this->mountingState = $mountingState;
    }

    /**
     * @return string|null
     */
    public function getEmbeddedAccessories(): ?string
    {
        return $this->embeddedAccessories;
    }

    /**
     * @param string|null $embeddedAccessories
     */
    public function setEmbeddedAccessories(?string $embeddedAccessories): void
    {
        $this->embeddedAccessories = $embeddedAccessories;
    }

    /**
     * @return string|null
     */
    public function getPackageNotIncluded(): ?string
    {
        return $this->packageNotIncluded;
    }

    /**
     * @param string|null $packageNotIncluded
     */
    public function setPackageNotIncluded(?string $packageNotIncluded): void
    {
        $this->packageNotIncluded = $packageNotIncluded;
    }

    /**
     * @return int|null
     */
    public function getPackagingUnit(): ?int
    {
        return $this->packagingUnit;
    }

    /**
     * @param int|null $packagingUnit
     */
    public function setPackagingUnit(?int $packagingUnit): void
    {
        $this->packagingUnit = $packagingUnit;
    }

    /**
     * @return float|null
     */
    public function getTotalPackageWeight(): ?float
    {
        return $this->totalPackageWeight;
    }

    /**
     * @param float|null $totalPackageWeight
     */
    public function setTotalPackageWeight(?float $totalPackageWeight): void
    {
        $this->totalPackageWeight = $totalPackageWeight;
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
