<?php

declare(strict_types=1);

namespace App\Model\Product\Detail;

use App\Model\Category\Listed\CategoryViewFacade;
use App\Model\Product\Availability\ProductStockAvailabilityInformation;
use App\Model\Product\Parameter\ParameterValuesViewFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFactory;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;
use Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory as BaseProductDetailViewElasticsearchFactory;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory;

class ProductDetailViewElasticsearchFactory extends BaseProductDetailViewElasticsearchFactory
{
    /**
     * @var \App\Model\Category\Listed\CategoryViewFacade
     */
    protected CategoryViewFacade $categoryViewFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterValuesViewFactory
     */
    protected ParameterValuesViewFactory $parameterValuesViewFactory;

    /**
     * @var \App\Model\Product\Detail\ProductFileViewFactory
     */
    protected ProductFileViewFactory $productFileViewFactory;

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
     * @param \App\Model\Category\Listed\CategoryViewFacade $categoryViewFacade
     * @param \App\Model\Product\Parameter\ParameterValuesViewFactory $parameterValuesViewFactory
     * @param \App\Model\Product\Detail\ProductFileViewFactory $productFileViewFactory
     */
    public function __construct(
        ImageViewFacadeInterface $imageViewFacade,
        CurrentCustomerUser $currentCustomerUser,
        ProductActionViewFactory $productActionViewFactory,
        ParameterViewFactory $parameterViewFactory,
        BrandViewFactory $brandViewFactory,
        SeoSettingFacade $seoSettingFacade,
        Domain $domain,
        ProductElasticsearchProvider $productElasticsearchProvider,
        ListedProductViewFactory $listedProductViewFactory,
        PriceFactory $priceFactory,
        CategoryViewFacade $categoryViewFacade,
        ParameterValuesViewFactory $parameterValuesViewFactory,
        ProductFileViewFactory $productFileViewFactory
    ) {
        parent::__construct(
            $imageViewFacade,
            $currentCustomerUser,
            $productActionViewFactory,
            $parameterViewFactory,
            $brandViewFactory,
            $seoSettingFacade,
            $domain,
            $productElasticsearchProvider,
            $listedProductViewFactory,
            $priceFactory
        );

        $this->categoryViewFacade = $categoryViewFacade;
        $this->parameterValuesViewFactory = $parameterValuesViewFactory;
        $this->productFileViewFactory = $productFileViewFactory;
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Brand\BrandView $brandView
     * @param \App\Model\Product\Listed\ListedProductView[] $accessories
     * @param \App\Model\Product\Listed\ListedProductView[] $variants
     * @return \App\Model\Product\Detail\ProductDetailView
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
            $this->priceFactory->createProductPriceFromArrayByPricingGroup(
                $productArray['prices'],
                $this->currentCustomerUser->getPricingGroup()
            ),
            $productArray['catnum'],
            $productArray['partno'],
            $productArray['ean'],
            $productArray['main_category_id'],
            $productArray['calculated_selling_denied'],
            $productArray['in_stock'],
            $productArray['is_main_variant'],
            $productArray['main_variant_id'],
            $productArray['flags'],
            $productArray['seo_title'] ?: $productArray['name'],
            $this->getSeoMetaDescription($productArray),
            $this->productActionViewFactory->createFromArray($productArray),
            $brandView,
            $this->getMainImageView($imageViews),
            $imageViews,
            $parameterViews,
            $accessories,
            $variants
        );
    }

    /**
     * @param array $productArray
     * @return \App\Model\Product\Detail\ProductDetailView
     */
    public function createFromProductArray(array $productArray): ProductDetailView
    {
        /** @var \App\Model\Product\Detail\ProductDetailView $productDetailView */
        $productDetailView = parent::createFromProductArray($productArray);

        $productDetailView->nameFirstLine = $productArray['name_prefix'];
        $productDetailView->nameSecondLine = $this->getNameSecondLine($productArray);
        $productDetailView->fullname = $this->getFullname($productArray);
        $productDetailView->usps = $productArray['usps'];
        $productDetailView->availabilityStatus = $productArray['availability_status'];
        $productDetailView->availableStocksCountInformation = $productArray['product_available_stocks_count_information'];
        $productDetailView->countExposedInStores = $productArray['product_count_exposed_in_stores'];
        $productDetailView->stocksAvailabilitiesInformation = $this->createProductStockAvailabilitiesInformationFromArray($productArray['stock_availabilities_information']);
        $productDetailView->mainCategoryPath = $productArray['main_category_path'];
        $productDetailView->dimensionParameterViews = $this->parameterValuesViewFactory->getDimensionParametersFromArray($productArray['parameters']);
        $productDetailView->nonDimensionParameterViews = $this->parameterValuesViewFactory->getNonDimensionParametersFromArray($productArray['parameters']);
        $productDetailView->categoryViews = $this->categoryViewFacade->getByCategoryIds($productArray['categories']);
        $productDetailView->productFileViews = $this->productFileViewFactory->createMultipleFromArray($productArray['files']);

        return $productDetailView;
    }

    /**
     * @param array $productArray
     * @return string
     */
    private function getNameSecondLine(array $productArray): string
    {
        return trim(
            $productArray['name']
            . ' '
            . $productArray['name_sufix']
        );
    }

    /**
     * @param array $productArray
     * @return string
     */
    private function getFullname(array $productArray): string
    {
        return trim(
            $productArray['name_prefix']
            . ' '
            . $productArray['name']
            . ' '
            . $productArray['name_sufix']
        );
    }

    /**
     * @param array $productStockAvailabilitiesInformationArray
     * @return \App\Model\Product\Availability\ProductStockAvailabilityInformation[]
     */
    private function createProductStockAvailabilitiesInformationFromArray(array $productStockAvailabilitiesInformationArray): array
    {
        $result = [];

        foreach ($productStockAvailabilitiesInformationArray as $item) {
            $result[] = new ProductStockAvailabilityInformation(
                $item['stock_name'],
                $item['stock_id'],
                $item['availability_information'],
                $item['exposed'],
                $item['availability_status']
            );
        }

        return $result;
    }
}
