<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductVariantsViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface;

class ProductDetailViewFactory
{
    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface $brandViewFacade
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface $parameterViewFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface $listedProductViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductVariantsViewFacadeInterface $listedProductVariantsViewFacade
     */
    public function __construct(
        protected readonly ImageViewFacadeInterface $imageViewFacade,
        protected readonly ProductActionViewFacadeInterface $productActionViewFacade,
        protected readonly BrandViewFacadeInterface $brandViewFacade,
        protected readonly ParameterViewFacadeInterface $parameterViewFacade,
        protected readonly Domain $domain,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly ListedProductViewFacadeInterface $listedProductViewFacade,
        protected readonly ListedProductVariantsViewFacadeInterface $listedProductVariantsViewFacade
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function createFromProduct(Product $product): ProductDetailView
    {
        $imageViews = $this->imageViewFacade->getAllImagesByEntityId(Product::class, $product->getId());
        $productActionView = $this->productActionViewFacade->getForProduct($product);
        $brandView = $this->brandViewFacade->findByProductId($product->getId());
        $parameterViews = $this->parameterViewFacade->getAllByProductId($product->getId());
        $accessories = $this->listedProductViewFacade->getAllAccessories($product->getId());
        $variants = $this->listedProductVariantsViewFacade->getAllVariants($product->getId());

        return $this->createInstance(
            $product,
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->categoryFacade->getProductMainCategoryOnCurrentDomain($product)->getId(),
            $imageViews,
            $brandView,
            $productActionView,
            $parameterViews,
            $accessories,
            $variants
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null $sellingPrice
     * @param int $mainCategoryId
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $galleryImageViews
     * @param \Shopsys\ReadModelBundle\Brand\BrandView|null $brandView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $accessories
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $variants
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    protected function createInstance(
        Product $product,
        ?ProductPrice $sellingPrice,
        int $mainCategoryId,
        array $galleryImageViews,
        ?BrandView $brandView,
        ProductActionView $productActionView,
        array $parameterViews,
        array $accessories,
        array $variants
    ): ProductDetailView {
        $domainId = $this->domain->getId();
        $locale = $this->domain->getLocale();

        return new ProductDetailView(
            $product->getId(),
            $product->getSeoH1($domainId) ?: $product->getName($locale),
            $product->getDescription($domainId),
            $product->getCalculatedAvailability()->getName($locale),
            $sellingPrice,
            $product->getCatnum(),
            $product->getPartno(),
            $product->getEan(),
            $mainCategoryId,
            $product->getCalculatedSellingDenied(),
            $this->isProductInStock($product),
            $product->isMainVariant(),
            $product->isVariant() ? $product->getMainVariant()->getId() : null,
            $this->getFlagIdsForProduct($product),
            $product->getSeoTitle($domainId) ?: $product->getName($locale),
            $this->getSeoMetaDescription($product),
            $productActionView,
            $brandView,
            $this->getMainImageView($galleryImageViews),
            $galleryImageViews,
            $parameterViews,
            $accessories,
            $variants
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function getFlagIdsForProduct(Product $product): array
    {
        $flagIds = [];

        foreach ($product->getFlags() as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    protected function getSeoMetaDescription(Product $product): string
    {
        $seoMetaDescription = $product->getSeoMetaDescription($this->domain->getId());

        if ($seoMetaDescription === null) {
            $seoMetaDescription = $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId());
        }

        return $seoMetaDescription;
    }

    /**
     * @param array $imageViews
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
     */
    protected function getMainImageView(array $imageViews): ?ImageView
    {
        return Utils::getArrayValue($imageViews, 0, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    protected function isProductInStock(Product $product): bool
    {
        return $product->getCalculatedAvailability()->getDispatchTime() === 0;
    }
}
