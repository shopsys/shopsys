<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Exception\DeprecatedAvailabilityPropertyFromProductException;
use App\Model\Product\Exception\ProductCannotBeTransformedException;
use App\Model\Product\Type\ProductType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 * @property \App\Model\Product\Brand\Brand|null $brand
 * @property \App\Model\Product\Product[]|\Doctrine\Common\Collections\Collection $variants
 * @property \App\Model\Product\Product|null $mainVariant
 * @method static \App\Model\Product\Product create(\App\Model\Product\ProductData $productData)
 * @method static \App\Model\Product\Product createMainVariant(\App\Model\Product\ProductData $productData, \App\Model\Product\Product[] $variants)
 * @method \App\Model\Category\Category[][] getCategoriesIndexedByDomainId()
 * @method \App\Model\Product\Brand\Brand|null getBrand()
 * @method \App\Model\Product\Product getMainVariant()
 * @method \App\Model\Product\Product[] getVariants()
 * @method addVariants(\App\Model\Product\Product[] $variants)
 * @method setMainVariant(\App\Model\Product\Product $mainVariant)
 * @method refreshVariants(\App\Model\Product\Product[] $currentVariants)
 * @method addNewVariants(\App\Model\Product\Product[] $currentVariants)
 * @method unsetRemovedVariants(\App\Model\Product\Product[] $currentVariants)
 * @method \App\Model\Product\ProductTranslation translation(?string $locale = null)
 * @property \App\Model\Product\ProductTranslation[]|\Doctrine\Common\Collections\Collection $translations
 * @property \App\Model\Product\ProductDomain[]|\Doctrine\Common\Collections\Collection $domains
 * @method \App\Model\Product\ProductDomain getProductDomain(int $domainId)
 * @property \App\Model\Product\Flag\Flag[]|\Doctrine\Common\Collections\Collection $flags
 */
class Product extends BaseProduct
{
    public const PDF_SUFFIX = '.pdf';
    public const FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE = 'assemblyInstruction';
    public const FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE = 'productTypePlan';
    public const OVERSIZED_PRODUCT_TYPE_ID = 1;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $downloadAssemblyInstructionFiles;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $downloadProductTypePlanFiles;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $preorder;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $vendorDeliveryDate;

    /**
     * @var \App\Model\Product\Parameter\Parameter[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Parameter\Parameter")
     * @ORM\JoinTable(name="product_variant_parameters",
     *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parameter_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    protected $variantParameters;

    /**
     * @var \App\Model\Product\Product|null
     *
     * @ORM\OneToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(name="default_variant_id", referencedColumnName="id", nullable=true)
     */
    protected $defaultVariant;

    /**
     * REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade.
     *
     * @var null
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $outOfStockAction;

    /**
     * REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade.
     *
     * @var null
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $outOfStockAvailability;

    /**
     * REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade.
     *
     * @var null
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $stockQuantity;

    /**
     * REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade.
     *
     * @var bool
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $usingStock;

    /**
     * REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade.
     *
     * @var null
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $calculatedAvailability;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);

        $this->downloadAssemblyInstructionFiles = $productData->downloadAssemblyInstructionFiles;
        $this->downloadProductTypePlanFiles = $productData->downloadProductTypePlanFiles;
        $this->preorder = $productData->preorder;
        $this->vendorDeliveryDate = $productData->vendorDeliveryDate;
        $this->flags = new ArrayCollection();
        $this->variantParameters = new ArrayCollection($productData->variantParameters);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \App\Model\Product\ProductData $productData
     */
    public function edit(array $productCategoryDomains, BaseProductData $productData)
    {
        parent::edit($productCategoryDomains, $productData);

        $this->downloadAssemblyInstructionFiles = $productData->downloadAssemblyInstructionFiles;
        $this->downloadProductTypePlanFiles = $productData->downloadProductTypePlanFiles;
        $this->preorder = $productData->preorder;
        $this->vendorDeliveryDate = $productData->vendorDeliveryDate;
        $this->editVariantParameters($productData);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setTranslations(BaseProductData $productData)
    {
        parent::setTranslations($productData);

        foreach ($productData->namePrefix as $locale => $namePrefix) {
            $this->translation($locale)->setNamePrefix($namePrefix);
        }
        foreach ($productData->nameSufix as $locale => $nameSufix) {
            $this->translation($locale)->setNameSufix($nameSufix);
        }
    }

    /**
     * @return \App\Model\Product\ProductTranslation
     */
    protected function createTranslation()
    {
        return new ProductTranslation();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setDomains(BaseProductData $productData): void
    {
        parent::setDomains($productData);

        foreach ($this->domains as $productDomain) {
            $domainId = $productDomain->getDomainId();
            $productDomain->setShortDescriptionUsp1($productData->shortDescriptionUsp1[$domainId]);
            $productDomain->setShortDescriptionUsp2($productData->shortDescriptionUsp2[$domainId]);
            $productDomain->setShortDescriptionUsp3($productData->shortDescriptionUsp3[$domainId]);
            $productDomain->setShortDescriptionUsp4($productData->shortDescriptionUsp4[$domainId]);
            $productDomain->setShortDescriptionUsp5($productData->shortDescriptionUsp5[$domainId]);
            $productDomain->setLowPriceWithVat($productData->lowPriceWithVat[$domainId]);
            $productDomain->setHighPriceWithVat($productData->highPriceWithVat[$domainId]);
            $productDomain->calcSellingPriceWithVat();
            $productDomain->setProductType($productData->productType[$domainId]);
            $productDomain->setFlags($productData->flags[$domainId] ?? []);
            $productDomain->setSaleExclusion($productDomain->calcSaleExclusion($productData->flags[$domainId] ?? []));

            $productDomain->setEmbeddedAccessories($productData->embeddedAccessories[$domainId]);
            $productDomain->setPackageNotIncluded($productData->packageNotIncluded[$domainId]);

            $productDomain->setMountingState($productData->mountingState[$domainId]);
            $productDomain->setPackagingUnit($productData->packagingUnit[$domainId] !== null ? (int)$productData->packagingUnit[$domainId] : null);
            $productDomain->setCountPackages($productData->countPackages[$domainId] !== null ? (int)$productData->countPackages[$domainId] : null);
            $productDomain->setTotalPackageWeight($productData->totalPackageWeight[$domainId] !== null ? (float)$productData->totalPackageWeight[$domainId] : null);
            $productDomain->setDomainHidden($productData->domainHidden[$domainId] ?? false);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function editVariantParameters(ProductData $productData): void
    {
        $this->variantParameters->clear();
        foreach ($productData->variantParameters as $variantParameter) {
            $this->variantParameters->add($variantParameter);
        }
    }

    /**
     * @param \App\Model\Product\ProductFilesData $productFilesData
     */
    public function editFileAttributes(ProductFilesData $productFilesData): void
    {
        foreach ($this->domains as $productDomain) {
            $domainId = $productDomain->getDomainId();
            if ($this->getAssemblyInstructionCode($domainId) !== $productFilesData->assemblyInstructionCode[$domainId]) {
                $productDomain->setAssemblyInstructionCode($productFilesData->assemblyInstructionCode[$domainId]);
                $this->setDownloadAssemblyInstructionFiles(true);
            }

            if ($this->getProductTypePlanCode($domainId) !== $productFilesData->productTypePlanCode[$domainId]) {
                $productDomain->setProductTypePlanCode($productFilesData->productTypePlanCode[$domainId]);
                $this->setDownloadProductTypePlanFiles(true);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function createDomains(BaseProductData $productData): void
    {
        $domainIds = array_keys($productData->seoTitles);
        foreach ($domainIds as $domainId) {
            $productDomain = new ProductDomain($this, $domainId);
            $this->domains->add($productDomain);
        }

        $this->setDomains($productData);
    }

    /**
     * @param \App\Model\Product\Product $variant
     */
    public function addVariant(BaseProduct $variant): void
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
            $variant->copyProductCategoryDomains($this->productCategoryDomains->toArray());
            if ($this->getDefaultVariant() === null) {
                $this->setDefaultVariant($variant);
            }
        }
    }

    /**
     * @param \App\Model\Product\Product $variant
     */
    public function setDefaultVariant(self $variant): void
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

        $this->defaultVariant = $variant;
    }

    /**
     * @return \App\Model\Product\Product|null
     */
    public function getDefaultVariant(): ?self
    {
        return $this->defaultVariant;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setAvailabilityAndStock(ProductData $productData): void
    {
        $this->availability = $productData->availability;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp1(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp1();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp2(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp2();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp3(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp3();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp4(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp4();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp5(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp5();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getLowPriceWithVat(int $domainId): ?Money
    {
        return $this->getProductDomain($domainId)->getLowPriceWithVat();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getHighPriceWithVat(int $domainId): ?Money
    {
        return $this->getProductDomain($domainId)->getHighPriceWithVat();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getSellingPriceWithVat(int $domainId): ?Money
    {
        return $this->getProductDomain($domainId)->getSellingPriceWithVat();
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlagsForDomain(int $domainId)
    {
        return $this->getProductDomain($domainId)->getFlags();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNamePrefix($locale = null): ?string
    {
        return $this->translation($locale)->getNamePrefix();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNameSufix($locale = null): ?string
    {
        return $this->translation($locale)->getNameSufix();
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getFullname(?string $locale = null): string
    {
        return trim(
            $this->getNamePrefix($locale)
            . ' '
            . $this->getName($locale)
            . ' '
            . $this->getNameSufix($locale)
        );
    }

    /**
     * @return string[]
     */
    public function getFullnames()
    {
        $fullnamesByLocale = [];

        foreach ($this->translations as $translation) {
            $fullnamesByLocale[$translation->getLocale()] = $this->getFullname($translation->getLocale());
        }

        return $fullnamesByLocale;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNameFirstLine(?string $locale = null): ?string
    {
        return $this->getNamePrefix($locale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getNameSecondLine(?string $locale = null): string
    {
        return trim(
            $this->getName($locale)
            . ' '
            . $this->getNameSufix($locale)
        );
    }

    /**
     * @return bool
     */
    public function isProductInSale(): bool
    {
        foreach ($this->getFlags() as $flag) {
            if ($flag->isSale()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $downloadAssemblyInstructionFiles
     */
    public function setDownloadAssemblyInstructionFiles(bool $downloadAssemblyInstructionFiles): void
    {
        $this->downloadAssemblyInstructionFiles = $downloadAssemblyInstructionFiles;
    }

    /**
     * @param bool $downloadProductTypePlanFiles
     */
    public function setDownloadProductTypePlanFiles(bool $downloadProductTypePlanFiles): void
    {
        $this->downloadProductTypePlanFiles = $downloadProductTypePlanFiles;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getAssemblyInstructionCode(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getAssemblyInstructionCode();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getProductTypePlanCode(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getProductTypePlanCode();
    }

    /**
     * @param int $domainId
     * @param string $type
     * @return string
     */
    public function getProductFileNameByType(int $domainId, string $type): string
    {
        return $type . '_' . $this->getId() . '_' . $domainId . self::PDF_SUFFIX;
    }

    /**
     * @return \App\Model\Product\ProductDomain[]|\Doctrine\Common\Collections\Collection
     */
    public function getProductDomains()
    {
        return $this->domains;
    }

    /**
     * @return bool
     */
    public function isDownloadAssemblyInstructionFiles(): bool
    {
        return $this->downloadAssemblyInstructionFiles;
    }

    /**
     * @return bool
     */
    public function isDownloadProductTypePlanFiles(): bool
    {
        return $this->downloadProductTypePlanFiles;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductType(int $domainId): ProductType
    {
        return $this->getProductDomain($domainId)->getProductType();
    }

    /**
     * @param int $domainId
     * @return bool|null
     */
    public function isMountingState(int $domainId): ?bool
    {
        return $this->getProductDomain($domainId)->isMountingState();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getEmbeddedAccessories(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getEmbeddedAccessories();
    }

    /**
     * @param int $domainId
     * @return int|null
     */
    public function getCountPackages(int $domainId): ?int
    {
        return $this->getProductDomain($domainId)->getCountPackages();
    }

    /**
     * @param int $domainId
     * @return int|null
     */
    public function getPackagingUnit(int $domainId): ?int
    {
        return $this->getProductDomain($domainId)->getPackagingUnit();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getPackageNotIncluded(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getPackageNotIncluded();
    }

    /**
     * @param int $domainId
     * @return float|null
     */
    public function getTotalPackageWeight(int $domainId): ?float
    {
        return $this->getProductDomain($domainId)->getTotalPackageWeight();
    }

    /**
     * @param int $domainId
     * @return bool|null
     */
    public function isDomainHidden(int $domainId): ?bool
    {
        return $this->getProductDomain($domainId)->isDomainHidden();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isOversized(int $domainId): bool
    {
        return $this->getProductType($domainId)->getId() === self::OVERSIZED_PRODUCT_TYPE_ID;
    }

    /**
     * @return  bool
     */
    public function hasPreorder(): bool
    {
        return $this->preorder;
    }

    /**
     * @param int $domainId
     * @return  bool
     */
    public function getSaleExclusion(int $domainId): bool
    {
        return $this->getProductDomain($domainId)->getSaleExclusion();
    }

    /**
     * @return int|null
     */
    public function getVendorDeliveryDate(): ?int
    {
        return $this->vendorDeliveryDate;
    }

    /**
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    public function getVariantParameters()
    {
        return $this->variantParameters->toArray();
    }

    /**
     * @param \App\Model\Product\Flag\Flag[] $flags
     */
    protected function editFlags(array $flags)
    {
        // Keep this function empty - flags were moved to Domain
    }

    /**
     * @return array
     */
    public function getFlags()
    {
        // Return empty array to override default functionality.
        // Flags were moved to Domain.
        return [];
    }

    /**
     * @return bool
     */
    public function isUsingStock()
    {
        //is always false and is by default set in migration to false.
        //removing old stock functionality means product.calculatedHidden is always setup by product.hidden
        return false;
    }

    /**
     * @return string
     */
    public function getOutOfStockAction()
    {
        throw new \Exception('deprecated - outOfStockAction');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     */
    public function getOutOfStockAvailability()
    {
        throw new DeprecatedAvailabilityPropertyFromProductException('outOfStockAvailability', $this->outOfStockAvailability);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     */
    public function getAvailability()
    {
        throw new DeprecatedAvailabilityPropertyFromProductException('availability', $this->availability);
    }

    /**
     * @return int|null
     */
    public function getStockQuantity()
    {
        //this getter isn't possible remove. Because is used in not-extendable code, just return default value.
        return null;
    }

    public function getCalculatedAvailability()
    {
        throw new DeprecatedAvailabilityPropertyFromProductException('calculatedAvailability', $this->calculatedAvailability);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    public function setProductCategoryDomains(array $productCategoryDomains)
    {
        foreach ($this->productCategoryDomains as $productCategoryDomain) {
            if ($this->isProductCategoryDomainInArray($productCategoryDomain, $productCategoryDomains) === false) {
                $this->productCategoryDomains->removeElement($productCategoryDomain);
            }
        }
        foreach ($productCategoryDomains as $productCategoryDomain) {
            if ($this->isProductCategoryDomainInArray($productCategoryDomain, $this->productCategoryDomains->toArray()) === false) {
                $this->productCategoryDomains->add($productCategoryDomain);
            }
        }
        if ($this->isMainVariant()) {
            foreach ($this->getVariants() as $variant) {
                $variant->copyProductCategoryDomains($productCategoryDomains);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain $searchProductCategoryDomain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @return bool
     */
    private function isProductCategoryDomainInArray(ProductCategoryDomain $searchProductCategoryDomain, array $productCategoryDomains)
    {
        foreach ($productCategoryDomains as $productCategoryDomain) {
            if ($productCategoryDomain->getCategory() === $searchProductCategoryDomain->getCategory()
                && $productCategoryDomain->getDomainId() === $searchProductCategoryDomain->getDomainId()
            ) {
                return true;
            }
        }
        return false;
    }

    public function setAsMainVariant(): void
    {
        if ($this->isMainVariant() || $this->isVariant()) {
            throw new ProductCannotBeTransformedException($this);
        }

        $this->variantType = self::VARIANT_TYPE_MAIN;
    }
}
