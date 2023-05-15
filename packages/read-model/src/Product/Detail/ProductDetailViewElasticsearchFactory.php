<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFactory;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory;

class ProductDetailViewElasticsearchFactory
{
    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory $parameterViewFactory
     * @param \Shopsys\ReadModelBundle\Brand\BrandViewFactory $brandViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     */
    public function __construct(
        protected readonly ImageViewFacadeInterface $imageViewFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductActionViewFactory $productActionViewFactory,
        protected readonly ParameterViewFactory $parameterViewFactory,
        protected readonly BrandViewFactory $brandViewFactory,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly ListedProductViewFactory $listedProductViewFactory,
        protected readonly PriceFactory $priceFactory
    ) {
    }

    /**
     * @param array $productArray
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function createFromProductArray(array $productArray): ProductDetailView
    {
        $parameterViews = [];

        foreach ($productArray['parameters'] as $parameterArray) {
            $parameterViews[] = $this->parameterViewFactory->createFromParameterArray($parameterArray);
        }

        return $this->createInstance(
            $productArray,
            $this->imageViewFacade->getAllImagesByEntityId(Product::class, $productArray['id']),
            $parameterViews,
            $this->brandViewFactory->createFromProductArray($productArray),
            $this->getListedProductViewsByProductIds($productArray['accessories']),
            $this->getListedProductViewsByProductIds($productArray['variants'])
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Brand\BrandView $brandView
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $accessories
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $variants
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    protected function createInstance(
        array $productArray,
        array $imageViews,
        array $parameterViews,
        BrandView $brandView,
        array $accessories,
        array $variants
    ): ProductDetailView {
        return new ProductDetailView(
            $productArray['id'],
            $productArray['seo_h1'] ?: $productArray['name'],
            $productArray['description'],
            $productArray['availability'],
            $productArray['catnum'],
            $productArray['partno'],
            $productArray['ean'],
            $productArray['calculated_selling_denied'],
            $productArray['in_stock'],
            $productArray['is_main_variant'],
            $productArray['flags'],
            $productArray['seo_title'] ?: $productArray['name'],
            $this->getSeoMetaDescription($productArray),
            $this->productActionViewFactory->createFromArray($productArray),
            $imageViews,
            $parameterViews,
            $accessories,
            $variants,
            $this->priceFactory->createProductPriceFromArrayByPricingGroup(
                $productArray['prices'],
                $this->currentCustomerUser->getPricingGroup()
            ),
            $productArray['main_category_id'],
            $productArray['main_variant_id'],
            $brandView,
            $this->getMainImageView($imageViews),
        );
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
     * @param array $productArray
     * @return string
     */
    protected function getSeoMetaDescription(array $productArray): string
    {
        $seoMetaDescription = $productArray['seo_meta_description'];

        if ($seoMetaDescription === null) {
            $seoMetaDescription = $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId());
        }

        return $seoMetaDescription;
    }

    /**
     * @param int[] $productIds
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    protected function getListedProductViewsByProductIds(array $productIds): array
    {
        if (count($productIds) === 0) {
            return [];
        }

        return $this->listedProductViewFactory->createFromProductsArray(
            $this->productElasticsearchProvider->getSellableProductArrayByIds($productIds)
        );
    }
}
