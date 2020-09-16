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

class ProductDetailViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    protected $seoSettingFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface
     */
    protected $productActionViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface
     */
    protected $brandViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface
     */
    protected $parameterViewFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface $brandViewFacade
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface $parameterViewFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     */
    public function __construct(
        ImageViewFacadeInterface $imageViewFacade,
        ProductActionViewFacadeInterface $productActionViewFacade,
        BrandViewFacadeInterface $brandViewFacade,
        ParameterViewFacadeInterface $parameterViewFacade,
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        CategoryFacade $categoryFacade,
        SeoSettingFacade $seoSettingFacade
    ) {
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFacade = $productActionViewFacade;
        $this->brandViewFacade = $brandViewFacade;
        $this->parameterViewFacade = $parameterViewFacade;
        $this->domain = $domain;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->categoryFacade = $categoryFacade;
        $this->seoSettingFacade = $seoSettingFacade;
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

        return $this->createInstance(
            $product,
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->categoryFacade->getProductMainCategoryOnCurrentDomain($product)->getId(),
            $imageViews,
            $brandView,
            $productActionView,
            $parameterViews
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
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    protected function createInstance(
        Product $product,
        ?ProductPrice $sellingPrice,
        int $mainCategoryId,
        array $galleryImageViews,
        ?BrandView $brandView,
        ProductActionView $productActionView,
        array $parameterViews
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
            $parameterViews
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
