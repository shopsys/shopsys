<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
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
 * @method int[] refreshVariants(\App\Model\Product\Product[] $currentVariants)
 * @method addNewVariants(\App\Model\Product\Product[] $currentVariants)
 * @method int[] unsetRemovedVariants(\App\Model\Product\Product[] $currentVariants)
 * @method \App\Model\Product\ProductTranslation translation(?string $locale = null)
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\ProductTranslation> $translations
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\ProductDomain> $domains
 * @method \App\Model\Product\ProductDomain getProductDomain(int $domainId)
 * @property \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
 * @method \Shopsys\FrameworkBundle\Model\Product\Unit\Unit getUnit()
 * @method \App\Model\Product\Flag\Flag[] getFlags(int $domainId)
 * @method setDomains(\App\Model\Product\ProductData $productData)
 * @method \App\Model\Product\ProductDomain[] getProductDomains()
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Transport\Transport> $excludedTransports
 * @method setExcludedTransports(\App\Model\Transport\Transport[] $excludedTransports)
 * @method \App\Model\Transport\Transport[] getExcludedTransports()
 * @method setTranslations(\App\Model\Product\ProductData $productData)
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
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Product\Product>
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
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \App\Model\Product\ProductData $productData
     */
    #[Override]
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
    #[Override]
    protected function setData(BaseProductData $productData): void
    {
        parent::setData($productData);

        $this->relatedProducts = new ArrayCollection($productData->relatedProducts);
    }

    /**
     * @return \App\Model\Product\ProductTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new ProductTranslation();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    #[Override]
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
    #[Override]
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
     * @return bool
     */
    public function getCalculatedSaleExclusion(int $domainId): bool
    {
        return $this->getProductDomain($domainId)->getCalculatedSaleExclusion();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     */
    #[Override]
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
    #[Override]
    public function getCatnum(): string
    {
        return $this->catnum;
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
