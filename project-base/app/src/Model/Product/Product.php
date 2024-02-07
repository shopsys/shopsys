<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Exception\DeprecatedAvailabilityPropertyFromProductException;
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
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\Product> $variants
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
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\ProductTranslation> $translations
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\ProductDomain> $domains
 * @method \App\Model\Product\ProductDomain getProductDomain(int $domainId)
 * @property \App\Model\Product\Unit\Unit $unit
 * @method \App\Model\Product\Unit\Unit getUnit()
 * @method \App\Model\Product\Flag\Flag[] getFlags(int $domainId)
 * @method setDomains(\App\Model\Product\ProductData $productData)
 */
class Product extends BaseProduct
{
    public const PDF_SUFFIX = '.pdf';

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    protected $catnum;

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
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $weight;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Product\Product>
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Product")
     * @ORM\JoinTable(name="related_products",
     *     joinColumns={@ORM\JoinColumn(name="main_product", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="related_product", referencedColumnName="id")}
     * )
     */
    protected $relatedProducts;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\ProductVideo\ProductVideo>
     * @ORM\OneToMany(
     *   targetEntity="App\Model\ProductVideo\ProductVideo",
     *   mappedBy="product",
     *   orphanRemoval=true,
     *   cascade={"persist"}
     * )
     */
    private $productVideos;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);

        $this->relatedProducts = new ArrayCollection();
        $this->productVideos = new ArrayCollection();
    }

    /**
     * @return \App\Model\ProductVideo\ProductVideo[]
     */
    public function getProductVideos(): array
    {
        return $this->productVideos->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<int, \App\Model\ProductVideo\ProductVideo> $productVideos
     */
    public function setProductVideos(ArrayCollection $productVideos): void
    {
        $this->productVideos = $productVideos;
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
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setData(BaseProductData $productData): void
    {
        parent::setData($productData);

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
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setAvailabilityAndStock(ProductData $productData): void
    {
        $this->availability = $productData->availability;
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
            },
        ));
    }

    /**
     * @param int $domainId
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
            . $this->getNameSufix($locale),
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
            . $this->getNameSufix($locale),
        );
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
     * @return \App\Model\Product\ProductDomain[]
     */
    public function getProductDomains(): array
    {
        return $this->domains->getValues();
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
     * @return bool
     */
    public function isUsingStock()
    {
        //is always false and is by default set in migration to false.
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    public function setProductCategoryDomains($productCategoryDomains)
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
        foreach ($this->getFlags($domainId) as $flag) {
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
