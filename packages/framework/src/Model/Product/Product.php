<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductDomainNotFoundException;

/**
 * Product
 *
 * @ORM\Table(
 *     name="products",
 *     indexes={
 *         @ORM\Index(columns={"variant_type"})
 *     }
 * )
 * @ORM\Entity
 */
class Product extends AbstractTranslatableEntity
{
    const PRICE_CALCULATION_TYPE_AUTO = 'auto';
    const PRICE_CALCULATION_TYPE_MANUAL = 'manual';
    const OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY = 'setAlternateAvailability';
    const OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE = 'excludeFromSale';
    const OUT_OF_STOCK_ACTION_HIDE = 'hide';
    const VARIANT_TYPE_NONE = 'none';
    const VARIANT_TYPE_MAIN = 'main';
    const VARIANT_TYPE_VARIANT = 'variant';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\ProductTranslation")
     */
    protected $translations;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $catnum;

    /**
     * @var string
     *
     * @ORM\Column(type="tsvector", nullable=false)
     */
    protected $catnumTsvector;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $partno;

    /**
     * @var string
     *
     * @ORM\Column(type="tsvector", nullable=false)
     */
    protected $partnoTsvector;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $ean;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $price;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $vat;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $sellingFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $sellingTo;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $sellingDenied;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $calculatedSellingDenied;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $calculatedHidden;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $usingStock;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $stockQuantity;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Unit\Unit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=false)
     */
    protected $unit;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $outOfStockAction;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\Availability")
     * @ORM\JoinColumn(name="availability_id", referencedColumnName="id", nullable=true)
     */
    protected $availability;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\Availability")
     * @ORM\JoinColumn(name="out_of_stock_availability_id", referencedColumnName="id", nullable=true)
     */
    protected $outOfStockAvailability;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\Availability")
     * @ORM\JoinColumn(name="calculated_availability_id", referencedColumnName="id", nullable=false)
     */
    protected $calculatedAvailability;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" = true})
     */
    protected $recalculateAvailability;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $calculatedVisibility;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[]
     *
     * @ORM\OneToMany(
     *   targetEntity="Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain",
     *   mappedBy="product",
     *   orphanRemoval=true,
     *   cascade={"persist"}
     * )
     */
    protected $productCategoryDomains;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     *
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinTable(name="product_flags")
     */
    protected $flags;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $priceCalculationType;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" = true})
     */
    protected $recalculatePrice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" = true})
     */
    protected $recalculateVisibility;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\Brand")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $brand;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\Shopsys\FrameworkBundle\Model\Product\Product[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", mappedBy="mainVariant", cascade={"persist"})
     */
    protected $variants;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="variants", cascade={"persist"})
     * @ORM\JoinColumn(name="main_variant_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $mainVariant;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    protected $variantType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDomain[]|\Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\ProductDomain", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, array $variants = null)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->catnum = $productData->catnum;
        $this->partno = $productData->partno;
        $this->ean = $productData->ean;
        $this->priceCalculationType = $productData->priceCalculationType;
        if ($this->getPriceCalculationType() === self::PRICE_CALCULATION_TYPE_AUTO) {
            $this->setPrice($productData->price);
        } else {
            $this->setPrice(null);
        }

        $this->vat = $productData->vat;
        $this->sellingFrom = $productData->sellingFrom;
        $this->sellingTo = $productData->sellingTo;
        $this->sellingDenied = $productData->sellingDenied;
        $this->hidden = $productData->hidden;
        $this->usingStock = $productData->usingStock;
        $this->stockQuantity = $productData->stockQuantity;
        $this->unit = $productData->unit;
        $this->outOfStockAction = $productData->outOfStockAction;
        $this->availability = $productData->availability;
        $this->outOfStockAvailability = $productData->outOfStockAvailability;
        $this->calculatedAvailability = $this->availability;
        $this->recalculateAvailability = true;
        $this->calculatedVisibility = false;
        $this->setTranslations($productData);
        $this->createDomains($productData);
        $this->productCategoryDomains = new ArrayCollection();
        $this->flags = new ArrayCollection($productData->flags);
        $this->recalculatePrice = true;
        $this->recalculateVisibility = true;
        $this->calculatedHidden = true;
        $this->calculatedSellingDenied = true;
        $this->brand = $productData->brand;
        $this->orderingPriority = $productData->orderingPriority;

        $this->variants = new ArrayCollection();
        if ($variants === null) {
            $this->variantType = self::VARIANT_TYPE_NONE;
        } else {
            $this->variantType = self::VARIANT_TYPE_MAIN;
            $this->addVariants($variants);
        }
    }

    public static function create(ProductData $productData): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return new self($productData, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    public static function createMainVariant(ProductData $productData, array $variants): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return new self($productData, $variants);
    }

    public function edit(
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        ProductData $productData
    ): void {
        $this->vat = $productData->vat;
        $this->sellingFrom = $productData->sellingFrom;
        $this->sellingTo = $productData->sellingTo;
        $this->sellingDenied = $productData->sellingDenied;
        $this->recalculateAvailability = true;
        $this->hidden = $productData->hidden;
        $this->editFlags($productData->flags);
        $this->brand = $productData->brand;
        $this->unit = $productData->unit;
        $this->setTranslations($productData);
        $this->setDomains($productData);

        if (!$this->isVariant()) {
            $this->setCategories($productCategoryDomainFactory, $productData->categoriesByDomainId);
        }
        if (!$this->isMainVariant()) {
            $this->usingStock = $productData->usingStock;
            $this->stockQuantity = $productData->stockQuantity;
            $this->outOfStockAction = $productData->outOfStockAction;
            $this->availability = $productData->availability;
            $this->outOfStockAvailability = $productData->outOfStockAvailability;
            $this->catnum = $productData->catnum;
            $this->partno = $productData->partno;
            $this->ean = $productData->ean;
            $this->priceCalculationType = $productData->priceCalculationType;
            if ($this->getPriceCalculationType() === self::PRICE_CALCULATION_TYPE_AUTO) {
                $this->setPrice($productData->price);
            } else {
                $this->setPrice(null);
            }
        }

        $this->orderingPriority = $productData->orderingPriority;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flags
     */
    protected function editFlags(array $flags): void
    {
        $this->flags->clear();
        foreach ($flags as $flag) {
            $this->flags->add($flag);
        }
    }

    public function changeVat(Vat $vat): void
    {
        $this->vat = $vat;
        $this->recalculatePrice = true;
    }

    /**
     * @param string|null $price
     */
    public function setPrice(?string $price): void
    {
        $this->price = Utils::ifNull($price, 0);
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param string|null $locale
     */
    public function getVariantAlias(?string $locale = null): ?string
    {
        return $this->translation($locale)->getVariantAlias();
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    public function getCatnum(): ?string
    {
        return $this->catnum;
    }

    public function getPartno(): ?string
    {
        return $this->partno;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        return $this->vat;
    }

    public function getSellingFrom(): ?DateTime
    {
        return $this->sellingFrom;
    }

    public function getSellingTo(): ?DateTime
    {
        return $this->sellingTo;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getCalculatedHidden(): bool
    {
        return $this->calculatedHidden;
    }

    public function isSellingDenied(): bool
    {
        return $this->sellingDenied;
    }

    public function getCalculatedSellingDenied(): bool
    {
        return $this->calculatedSellingDenied;
    }

    public function isUsingStock(): bool
    {
        return $this->usingStock;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function getUnit(): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        return $this->unit;
    }

    public function getOutOfStockAction(): string
    {
        return $this->outOfStockAction;
    }

    public function getAvailability(): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        return $this->availability;
    }

    public function getOutOfStockAvailability(): ?\Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        return $this->outOfStockAvailability;
    }

    public function getCalculatedAvailability(): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        return $this->calculatedAvailability;
    }

    public function getOrderingPriority(): int
    {
        return $this->orderingPriority;
    }

    public function setAvailability(Availability $availability): void
    {
        $this->availability = $availability;
        $this->recalculateAvailability = true;
    }

    public function setOutOfStockAvailability(Availability $outOfStockAvailability = null): void
    {
        $this->outOfStockAvailability = $outOfStockAvailability;
        $this->recalculateAvailability = true;
    }

    public function setCalculatedAvailability(Availability $calculatedAvailability): void
    {
        $this->calculatedAvailability = $calculatedAvailability;
        $this->recalculateAvailability = false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categoriesByDomainId
     */
    public function setCategories(
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        array $categoriesByDomainId
    ): void {
        foreach ($categoriesByDomainId as $domainId => $categories) {
            $this->removeOldProductCategoryDomains($categories, $domainId);
            $this->createNewProductCategoryDomains($productCategoryDomainFactory, $categories, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $newCategories
     */
    protected function createNewProductCategoryDomains(
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        array $newCategories,
        int $domainId
    ): void {
        $currentProductCategoryDomainsOnDomainByCategoryId = $this->getProductCategoryDomainsByDomainIdIndexedByCategoryId($domainId);

        foreach ($newCategories as $newCategory) {
            if (!array_key_exists($newCategory->getId(), $currentProductCategoryDomainsOnDomainByCategoryId)) {
                $productCategoryDomain = $productCategoryDomainFactory->create($this, $newCategory, $domainId);
                $this->productCategoryDomains->add($productCategoryDomain);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $newCategories
     */
    protected function removeOldProductCategoryDomains(array $newCategories, int $domainId): void
    {
        $currentProductCategoryDomains = $this->getProductCategoryDomainsByDomainIdIndexedByCategoryId($domainId);

        foreach ($currentProductCategoryDomains as $currentProductCategoryDomain) {
            if (!in_array($currentProductCategoryDomain->getCategory(), $newCategories, true)) {
                $this->productCategoryDomains->removeElement($currentProductCategoryDomain);
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[]
     */
    protected function getProductCategoryDomainsByDomainIdIndexedByCategoryId(int $domainId): array
    {
        $productCategoryDomainsByCategoryId = [];

        foreach ($this->productCategoryDomains as $productCategoryDomain) {
            if ($productCategoryDomain->getDomainId() === $domainId) {
                $productCategoryDomainsByCategoryId[$productCategoryDomain->getCategory()->getId()] = $productCategoryDomain;
            }
        }

        return $productCategoryDomainsByCategoryId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public function getCategoriesIndexedByDomainId()
    {
        $categoriesByDomainId = [];

        foreach ($this->productCategoryDomains as $productCategoryDomain) {
            $categoriesByDomainId[$productCategoryDomain->getDomainId()][] = $productCategoryDomain->getCategory();
        }

        return $categoriesByDomainId;
    }

    public function getPriceCalculationType(): string
    {
        return $this->priceCalculationType;
    }

    public function getBrand(): ?\Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        return $this->brand;
    }

    protected function getCalculatedVisibility(): bool
    {
        return $this->calculatedVisibility;
    }

    public function isVisible(): bool
    {
        return $this->getCalculatedVisibility();
    }

    public function markPriceAsRecalculated(): void
    {
        $this->recalculatePrice = false;
    }

    public function markForVisibilityRecalculation(): void
    {
        $this->recalculateVisibility = true;
    }

    public function markForAvailabilityRecalculation(): void
    {
        $this->recalculateAvailability = true;
    }

    public function isMainVariant(): bool
    {
        return $this->variantType === self::VARIANT_TYPE_MAIN;
    }

    public function isVariant(): bool
    {
        return $this->variantType === self::VARIANT_TYPE_VARIANT;
    }

    public function getMainVariant(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        if (!$this->isVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException();
        }

        return $this->mainVariant;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $variant
     */
    public function addVariant(self $variant): void
    {
        if (!$this->isMainVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException(
                $this->getId(),
                $variant->getId()
            );
        }
        if ($variant->isMainVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException($variant->getId());
        }
        if ($variant->isVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException($variant->getId());
        }

        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setMainVariant($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    protected function addVariants(array $variants): void
    {
        foreach ($variants as $variant) {
            $this->addVariant($variant);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getVariants(): array
    {
        return $this->variants->toArray();
    }

    public function unsetMainVariant(): void
    {
        if (!$this->isVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException();
        }
        $this->variantType = self::VARIANT_TYPE_NONE;
        $this->mainVariant->variants->removeElement($this);
        $this->mainVariant = null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     */
    protected function setMainVariant(self $mainVariant): void
    {
        $this->variantType = self::VARIANT_TYPE_VARIANT;
        $this->mainVariant = $mainVariant;
    }
    
    public function addStockQuantity(int $quantity): void
    {
        $this->stockQuantity += $quantity;
    }
    
    public function subtractStockQuantity(int $quantity): void
    {
        $this->stockQuantity -= $quantity;
    }

    protected function setTranslations(ProductData $productData): void
    {
        foreach ($productData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
        foreach ($productData->variantAlias as $locale => $variantAlias) {
            $this->translation($locale)->setVariantAlias($variantAlias);
        }
    }

    protected function setDomains(ProductData $productData): void
    {
        foreach ($this->domains as $productDomain) {
            $domainId = $productDomain->getDomainId();
            $productDomain->setSeoTitle($productData->seoTitles[$domainId]);
            $productDomain->setSeoH1($productData->seoH1s[$domainId]);
            $productDomain->setSeoMetaDescription($productData->seoMetaDescriptions[$domainId]);
            $productDomain->setDescription($productData->descriptions[$domainId]);
            $productDomain->setShortDescription($productData->shortDescriptions[$domainId]);
        }
    }

    protected function getProductDomain(int $domainId): \Shopsys\FrameworkBundle\Model\Product\ProductDomain
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        throw new ProductDomainNotFoundException($this->id, $domainId);
    }

    public function getShortDescription(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescription();
    }

    public function getDescription(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getDescription();
    }

    public function getSeoH1(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getSeoH1();
    }

    public function getSeoTitle(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getSeoTitle();
    }

    public function getSeoMetaDescription(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getSeoMetaDescription();
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Product\ProductTranslation
    {
        return new ProductTranslation();
    }

    protected function createDomains(ProductData $productData): void
    {
        $domainIds = array_keys($productData->seoTitles);

        foreach ($domainIds as $domainId) {
            $productDomain = new ProductDomain($this, $domainId);
            $this->domains[] = $productDomain;
        }

        $this->setDomains($productData);
    }
}
