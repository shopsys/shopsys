<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductCannotBeTransformedException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * Product
 *
 * @method \Shopsys\FrameworkBundle\Model\Product\ProductTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'products')]
#[ORM\Index(columns: ['variant_type'])]
#[ORM\Entity]
class Product extends AbstractTranslatableEntity
{
    public const VARIANT_TYPE_NONE = 'none';
    public const VARIANT_TYPE_MAIN = 'main';
    public const VARIANT_TYPE_VARIANT = 'variant';

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\ProductTranslation>
     */
    #[Prezent\Translations(targetEntity: ProductTranslation::class)]
    protected $translations;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $catnum;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $partno;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $ean;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $sellingFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $sellingTo;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $sellingDenied;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Unit::class)]
    protected $unit;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain>
     */
    #[ORM\OneToMany(targetEntity: ProductCategoryDomain::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist'])]
    protected $productCategoryDomains;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
     */
    #[ORM\JoinColumn(name: 'brand_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Brand::class)]
    protected $brand;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Product>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'mainVariant', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $variants;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    #[ORM\JoinColumn(name: 'main_variant_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'variants', cascade: ['persist'])]
    protected $mainVariant;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    protected $variantType;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\ProductDomain>
     */
    #[ORM\OneToMany(targetEntity: ProductDomain::class, mappedBy: 'product', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $weight;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\Transport>
     */
    #[ORM\JoinTable(name: 'product_excluded_transports')]
    #[ORM\ManyToMany(targetEntity: Transport::class)]
    protected $excludedTransports;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    protected $productType;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo>
     */
    #[ORM\OneToMany(targetEntity: ProductVideo::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist'])]
    protected $productVideos;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $isAllowedNegativeStock;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->excludedTransports = new ArrayCollection();
        $this->productVideos = new ArrayCollection();
        $this->catnum = $productData->catnum;
        $this->partno = $productData->partno;
        $this->ean = $productData->ean;
        $this->createDomains($productData);
        $this->productCategoryDomains = new ArrayCollection();

        $this->variants = new ArrayCollection();

        if ($variants === null) {
            $this->variantType = self::VARIANT_TYPE_NONE;
        } else {
            $this->variantType = self::VARIANT_TYPE_MAIN;
            $this->addVariants($variants);
        }

        $this->uuid = $productData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($productData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    public function edit(
        array $productCategoryDomains,
        ProductData $productData,
    ): void {
        $this->setDomains($productData);

        if (!$this->isVariant()) {
            $this->setProductCategoryDomains($productCategoryDomains);
        }

        if (!$this->isMainVariant()) {
            $this->catnum = $productData->catnum;
            $this->partno = $productData->partno;
            $this->ean = $productData->ean;
        }
        $this->setData($productData);
    }

    protected function setData(ProductData $productData): void
    {
        $this->sellingFrom = $productData->sellingFrom;
        $this->sellingTo = $productData->sellingTo;
        $this->sellingDenied = $productData->sellingDenied;
        $this->hidden = $productData->hidden;
        $this->brand = $productData->brand;
        $this->unit = $productData->unit;
        $this->weight = $productData->weight;
        $this->productType = $productData->productType;
        $this->isAllowedNegativeStock = $productData->isAllowedNegativeStock;
        $this->setTranslations($productData);
        $this->setExcludedTransports($productData->excludedTransports);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public static function create(ProductData $productData)
    {
        return new static($productData, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public static function createMainVariant(ProductData $productData, array $variants)
    {
        return new static($productData, $variants);
    }

    public function changeVatForDomain(Vat $vat, int $domainId): void
    {
        $this->getProductDomain($domainId)->setVat($vat);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getFullNames()
    {
        $fullNamesByLocale = [];

        foreach ($this->translations as $translation) {
            $fullNamesByLocale[$translation->getLocale()] = $this->getFullName($translation->getLocale());
        }

        return $fullNamesByLocale;
    }

    public function getFullName(?string $locale = null): ?string
    {
        return trim(
            $this->getNamePrefix($locale)
            . ' '
            . $this->getName($locale)
            . ' '
            . $this->getNameSuffix($locale),
        );
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getVariantAlias($locale = null)
    {
        return $this->translation($locale)->getVariantAlias();
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @return string|null
     */
    public function getCatnum()
    {
        return $this->catnum;
    }

    /**
     * @return string|null
     */
    public function getPartno()
    {
        return $this->partno;
    }

    /**
     * @return string|null
     */
    public function getEan()
    {
        return $this->ean;
    }

    public function calculateFreeQuantity(int $quantity, int $domainId): int
    {
        if ($this->getPromotionXy($domainId) === null) {
            return 0;
        }

        $buyQuantity = $this->getPromotionXy($domainId)->getBuyQuantity();
        $freeQuantity = $this->getPromotionXy($domainId)->getFreeQuantity();

        $totalPromotionsSize = $buyQuantity + $freeQuantity;

        $numberOfAppliedFullPromotions = intdiv($quantity, $totalPromotionsSize);
        $remainder = $quantity % $totalPromotionsSize;
        $extra = max(0, min($remainder - $buyQuantity, $freeQuantity));

        return $numberOfAppliedFullPromotions * $freeQuantity + $extra;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    public function getPromotionXy(int $domainId)
    {
        return $this->getProductDomain($domainId)->getPromotionXy();
    }

    public function getVatForDomain(int $domainId): Vat
    {
        return $this->getProductDomain($domainId)->getVat();
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSellingFrom()
    {
        return $this->sellingFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSellingTo()
    {
        return $this->sellingTo;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return bool
     */
    public function isSellingDenied()
    {
        return $this->sellingDenied;
    }

    /**
     * @param mixed|null $domainId
     * @return bool
     */
    public function isSellingDeniedOnDomain($domainId = null)
    {
        return $this->getProductDomain($domainId)->isSellingDenied();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isCalculatedSellingDenied($domainId)
    {
        return $this->getProductDomain($domainId)->isCalculatedSellingDenied();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getOrderingPriority(int $domainId)
    {
        return $this->getProductDomain($domainId)->getOrderingPriority();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    public function setProductCategoryDomains($productCategoryDomains): void
    {
        foreach ($this->productCategoryDomains as $productCategoryDomain) {
            if ($this->isProductCategoryDomainInArray($productCategoryDomain, $productCategoryDomains) === false) {
                $this->productCategoryDomains->removeElement($productCategoryDomain);
            }
        }

        foreach ($productCategoryDomains as $productCategoryDomain) {
            if ($this->isProductCategoryDomainInArray(
                $productCategoryDomain,
                $this->productCategoryDomains->getValues(),
            ) === false) {
                $this->productCategoryDomains->add($productCategoryDomain);
            }
        }

        if (!$this->isMainVariant()) {
            return;
        }

        foreach ($this->getVariants() as $variant) {
            $variant->copyProductCategoryDomains($productCategoryDomains);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    protected function isProductCategoryDomainInArray(
        ProductCategoryDomain $searchProductCategoryDomain,
        array $productCategoryDomains,
    ): bool {
        foreach ($productCategoryDomains as $productCategoryDomain) {
            if ($productCategoryDomain->getCategory() === $searchProductCategoryDomain->getCategory()
                && $productCategoryDomain->getDomainId() === $searchProductCategoryDomain->getDomainId()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool|null
     */
    public function isDomainHidden(int $domainId)
    {
        return $this->getProductDomain($domainId)->isDomainHidden();
    }

    public function setAsMainVariant(): void
    {
        if ($this->isMainVariant() || $this->isVariant()) {
            throw new ProductCannotBeTransformedException($this);
        }

        $this->variantType = static::VARIANT_TYPE_MAIN;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlags(int $domainId)
    {
        return $this->getProductDomain($domainId)->getFlags();
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]> $flagsByDomainId
     */
    public function setFlags($flagsByDomainId): void
    {
        foreach ($this->domains as $domain) {
            if (!array_key_exists($domain->getDomainId(), $flagsByDomainId)) {
                continue;
            }

            $domain->setFlags($flagsByDomainId[$domain->getDomainId()]);
        }
    }

    /**
     * @return int[]
     */
    public function getFlagsIdsForDomain(int $domainId): array
    {
        $flagIds = [];

        foreach ($this->getFlags($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return bool
     */
    public function isMainVariant()
    {
        return $this->variantType === self::VARIANT_TYPE_MAIN;
    }

    /**
     * @return bool
     */
    public function isVariant()
    {
        return $this->variantType === self::VARIANT_TYPE_VARIANT;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getMainVariant()
    {
        if (!$this->isVariant()) {
            throw new ProductIsNotVariantException();
        }

        return $this->mainVariant;
    }

    public function addVariant(self $variant): void
    {
        if (!$this->isMainVariant()) {
            throw new VariantCanBeAddedOnlyToMainVariantException(
                $this->getId(),
                $variant->getId(),
            );
        }

        if ($variant->isMainVariant()) {
            throw new MainVariantCannotBeVariantException($variant->getId());
        }

        if ($variant->isVariant()) {
            throw new ProductIsAlreadyVariantException($variant->getId());
        }

        if ($this->variants->contains($variant)) {
            return;
        }

        $this->variants->add($variant);
        $variant->setMainVariant($this);
        $variant->copyProductCategoryDomains($this->productCategoryDomains->getValues());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    protected function copyProductCategoryDomains(array $productCategoryDomains): void
    {
        $newProductCategoryDomains = [];

        foreach ($productCategoryDomains as $productCategoryDomain) {
            $copiedProductCategoryDomain = clone $productCategoryDomain;
            $copiedProductCategoryDomain->setProduct($this);
            $newProductCategoryDomains[] = $copiedProductCategoryDomain;
        }
        $this->setProductCategoryDomains($newProductCategoryDomains);
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
    public function getVariants()
    {
        return $this->variants->getValues();
    }

    public function unsetMainVariant(): void
    {
        if (!$this->isVariant()) {
            throw new ProductIsNotVariantException();
        }
        $this->variantType = self::VARIANT_TYPE_NONE;
        $this->mainVariant->variants->removeElement($this);
        $this->mainVariant = null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     */
    protected function setMainVariant($mainVariant): void
    {
        $this->variantType = self::VARIANT_TYPE_VARIANT;
        $this->mainVariant = $mainVariant;
    }

    protected function setTranslations(ProductData $productData): void
    {
        foreach ($productData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($productData->variantAlias as $locale => $variantAlias) {
            $this->translation($locale)->setVariantAlias($variantAlias);
        }

        foreach ($productData->namePrefix as $locale => $namePrefix) {
            $this->translation($locale)->setNamePrefix($namePrefix);
        }

        foreach ($productData->nameSuffix as $locale => $nameSuffix) {
            $this->translation($locale)->setNameSuffix($nameSuffix);
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
            $productDomain->setVat($productData->productInputPricesByDomain[$domainId]->vat);
            $productDomain->setSellingDenied($productData->domainSellingDenied[$domainId]);
            $productDomain->setShortDescriptionUsp1($productData->shortDescriptionUsp1ByDomainId[$domainId]);
            $productDomain->setShortDescriptionUsp2($productData->shortDescriptionUsp2ByDomainId[$domainId]);
            $productDomain->setShortDescriptionUsp3($productData->shortDescriptionUsp3ByDomainId[$domainId]);
            $productDomain->setShortDescriptionUsp4($productData->shortDescriptionUsp4ByDomainId[$domainId]);
            $productDomain->setShortDescriptionUsp5($productData->shortDescriptionUsp5ByDomainId[$domainId]);
            $productDomain->setFlags($productData->flagsByDomainId[$domainId] ?? []);
            $productDomain->setOrderingPriority((int)$productData->orderingPriorityByDomainId[$domainId]);
            $productDomain->setDomainHidden($productData->domainHidden[$domainId] ?? false);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductDomain
     */
    protected function getProductDomain(int $domainId)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('domainId', $domainId))
            ->setMaxResults(1);

        $result = $this->domains->matching($criteria)->first();

        if ($result === false) {
            throw new ProductDomainNotFoundException($domainId, $this->id);
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescription();
    }

    /**
     * @return string|null
     */
    public function getDescription(int $domainId)
    {
        return $this->getProductDomain($domainId)->getDescription();
    }

    public function getDescriptionAsPlainText(int $domainId): ?string
    {
        return TransformStringHelper::convertHtmlToPlainText($this->getDescription($domainId));
    }

    /**
     * @return string|null
     */
    public function getSeoH1(int $domainId)
    {
        return $this->getProductDomain($domainId)->getSeoH1();
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getProductDomain($domainId)->getSeoTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getProductDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null $promotionXy
     * @param int $domainId
     */
    public function setPromotionXy($promotionXy, $domainId): void
    {
        $this->getProductDomain($domainId)->setPromotionXy($promotionXy);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new ProductTranslation();
    }

    protected function createDomains(ProductData $productData): void
    {
        $domainIds = array_keys($productData->seoTitles);

        foreach ($domainIds as $domainId) {
            $productDomain = new ProductDomain($this, $domainId);
            $this->domains->add($productDomain);
        }

        $this->setDomains($productData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $currentVariants
     * @return int[]
     */
    public function refreshVariants(array $currentVariants): array
    {
        $removedVariantIds = $this->unsetRemovedVariants($currentVariants);
        $this->addNewVariants($currentVariants);

        return $removedVariantIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $currentVariants
     */
    protected function addNewVariants(array $currentVariants): void
    {
        foreach ($currentVariants as $currentVariant) {
            if (!in_array($currentVariant, $this->getVariants(), true)) {
                $this->addVariant($currentVariant);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $currentVariants
     * @return int[]
     */
    protected function unsetRemovedVariants(array $currentVariants): array
    {
        $removedVariantIds = [];

        foreach ($this->getVariants() as $originalVariant) {
            if (!in_array($originalVariant, $currentVariants, true)) {
                $originalVariant->unsetMainVariant();
                $removedVariantIds[] = $originalVariant->getId();
            }
        }

        return $removedVariantIds;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp1(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp1();
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp2(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp2();
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp3(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp3();
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp4(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp4();
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionUsp5(int $domainId)
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp5();
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductDomain[]
     */
    public function getProductDomains(): array
    {
        return $this->domains->getValues();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $excludedTransports
     */
    protected function setExcludedTransports($excludedTransports): void
    {
        foreach ($this->excludedTransports as $currentExcludedTransport) {
            if (!in_array($currentExcludedTransport, $excludedTransports, true)) {
                $this->excludedTransports->removeElement($currentExcludedTransport);
            }
        }

        foreach ($excludedTransports as $excludedTransport) {
            if (!$this->excludedTransports->contains($excludedTransport)) {
                $this->excludedTransports->add($excludedTransport);
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getExcludedTransports()
    {
        return $this->excludedTransports->getValues();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNamePrefix($locale = null)
    {
        return $this->translation($locale)->getNamePrefix();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNameSuffix($locale = null)
    {
        return $this->translation($locale)->getNameSuffix();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo[]
     */
    public function getProductVideos()
    {
        return $this->productVideos->getValues();
    }

    /**
     * @return bool
     */
    public function isAllowedNegativeStock()
    {
        return $this->isAllowedNegativeStock;
    }

    public function isSellableOnAllDomains(): bool
    {
        foreach ($this->domains as $domain) {
            if ($domain->isCalculatedSellingDenied()) {
                return false;
            }
        }

        return true;
    }

    public function isSellableOnAnyDomain(): bool
    {
        foreach ($this->domains as $domain) {
            if (!$domain->isCalculatedSellingDenied()) {
                return true;
            }
        }

        return false;
    }
}
