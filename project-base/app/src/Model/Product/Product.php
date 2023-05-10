<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Exception\DeprecatedAvailabilityPropertyFromProductException;
use App\Model\Product\Exception\ProductCannotBeTransformedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
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
 * @property \App\Model\Product\Unit\Unit $unit
 * @method \App\Model\Product\Unit\Unit getUnit()
 */
class Product extends BaseProduct
{
    public const PDF_SUFFIX = '.pdf';
    public const FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE = 'assemblyInstruction';
    public const FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE = 'productTypePlan';

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    protected $catnum;

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
     * @var null
     * @deprecated REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $outOfStockAction;

    /**
     * @var null
     * @deprecated REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $outOfStockAvailability;

    /**
     * @var null
     * @deprecated REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $stockQuantity;

    /**
     * @var bool
     * @deprecated REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $usingStock;

    /**
     * @var null
     * @deprecated REMOVED PROPERTY! This property is removed from model, new product stock management is in ProductAvailabilityFacade
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     * @phpstan-ignore-next-line Removed property
     */
    protected $calculatedAvailability;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $weight;

    /**
     * @var \App\Model\Product\Product[]|\Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Product")
     * @ORM\JoinTable(name="related_products",
     *     joinColumns={@ORM\JoinColumn(name="main_product", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="related_product", referencedColumnName="id")}
     * )
     */
    protected $relatedProducts;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);

        $this->flags = new ArrayCollection();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \App\Model\Product\ProductData $productData
     */
    public function edit(
        array $productCategoryDomains,
        BaseProductData $productData,
    ) {
        $this->editRelatedProducts($productData->relatedProducts);

        parent::edit($productCategoryDomains, $productData);

        $this->markForExport();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setData(BaseProductData $productData): void
    {
        parent::setData($productData);

        $this->downloadAssemblyInstructionFiles = $productData->downloadAssemblyInstructionFiles;
        $this->downloadProductTypePlanFiles = $productData->downloadProductTypePlanFiles;
        $this->preorder = $productData->preorder;
        $this->vendorDeliveryDate = $productData->vendorDeliveryDate;
        $this->weight = $productData->weight;
        $this->relatedProducts = new ArrayCollection($productData->relatedProducts);
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
            $productDomain->setFlags($productData->flags[$domainId] ?? []);
            $productDomain->setSaleExclusion($productData->saleExclusion[$domainId]);
            $productDomain->setDomainHidden($productData->domainHidden[$domainId] ?? false);
            $productDomain->setDomainOrderingPriority((int)$productData->domainOrderingPriority[$domainId]);
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

            if ($this->getProductTypePlanCode($domainId) === $productFilesData->productTypePlanCode[$domainId]) {
                continue;
            }

            $productDomain->setProductTypePlanCode($productFilesData->productTypePlanCode[$domainId]);
            $this->setDownloadProductTypePlanFiles(true);
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
            throw new VariantCanBeAddedOnlyToMainVariantException(
                $this->getId(),
                $variant->getId()
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
     * @return string[]
     */
    public function getAllNonEmptyShortDescriptionUsp(int $domainId): array
    {
        $usps = [
            $this->getShortDescriptionUsp1($domainId),
            $this->getShortDescriptionUsp2($domainId),
            $this->getShortDescriptionUsp3($domainId),
            $this->getShortDescriptionUsp4($domainId),
            $this->getShortDescriptionUsp5($domainId),
        ];

        return array_values(array_filter(
            $usps,
            static function ($value) {
                return $value !== null && $value !== '';
            }
        ));
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
     * @param int $domainId
     * @return int[]
     */
    public function getFlagsIdsForDomain(int $domainId): array
    {
        $flagIds = [];
        foreach ($this->getFlagsForDomain($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
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
     * @return bool|null
     */
    public function isDomainHidden(int $domainId): ?bool
    {
        return $this->getProductDomain($domainId)->isDomainHidden();
    }

    /**
     * @return  bool
     */
    public function hasPreorder(): bool
    {
        $result = $this->preorder;
        if ($this->isMainVariant()) {
            foreach ($this->getVariants() as $variant) {
                $result = $result || $variant->hasPreorder();
            }
        }

        return $result;
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
     * @param int $domainId
     * @return bool
     */
    public function getCalculatedSaleExclusion(int $domainId): bool
    {
        return $this->getProductDomain($domainId)->getCalculatedSaleExclusion();
    }

    /**
     * @return int|null
     */
    public function getVendorDeliveryDate(): ?int
    {
        return $this->vendorDeliveryDate;
    }

    /**
     * @param int $domainId
     * @return  int
     */
    public function getDomainOrderingPriority(int $domainId): int
    {
        return $this->getProductDomain($domainId)->getDomainOrderingPriority();
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
        throw new Exception('deprecated - outOfStockAction');
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
            if ($this->isProductCategoryDomainInArray($productCategoryDomain, $this->productCategoryDomains->getValues()) === false) {
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

    public function setAsMainVariant(): void
    {
        if ($this->isMainVariant() || $this->isVariant()) {
            throw new ProductCannotBeTransformedException($this);
        }

        $this->variantType = self::VARIANT_TYPE_MAIN;
    }

    /**
     * @return string
     */
    public function getCatnum(): string
    {
        return $this->catnum;
    }

    /**
     * @param string $akeneoCode
     * @param int $domainId
     * @return bool
     */
    public function hasFlagByAkeneoCodeForDomain(string $akeneoCode, int $domainId): bool
    {
        foreach ($this->getFlagsForDomain($domainId) as $flag) {
            if ($flag->getAkeneoCode() === $akeneoCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @return \App\Model\Product\Product[]
     */
    public function getRelatedProducts(): array
    {
        return $this->relatedProducts->getValues();
    }

    /**
     * @param \App\Model\Product\Product[] $relatedProducts
     */
    protected function editRelatedProducts(array $relatedProducts)
    {
        $this->relatedProducts->clear();
        foreach ($relatedProducts as $relatedProduct) {
            $this->relatedProducts->add($relatedProduct);
        }
    }
}
