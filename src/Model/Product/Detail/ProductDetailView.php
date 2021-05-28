<?php

declare(strict_types=1);

namespace App\Model\Product\Detail;

use Shopsys\ReadModelBundle\Product\Detail\ProductDetailView as BaseProductDetailView;

/**
 * @property \App\Model\Product\Listed\ListedProductView[] $accessories
 * @method \App\Model\Product\Listed\ListedProductView[] getAccessories()
 */
class ProductDetailView extends BaseProductDetailView
{
    /**
     * @var string
     */
    public string $nameFirstLine;

    /**
     * @var string
     */
    public string $nameSecondLine;

    /**
     * @var string
     */
    public string $fullname;

    /**
     * @var string[]
     */
    public array $usps;

    /**
     * @var \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public array $nonDimensionParameterViews;

    /**
     * @var \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public array $dimensionParameterViews;

    /**
     * @var string
     */
    public string $availabilityStatus;

    /**
     * @var string
     */
    public string $availableStocksCountInformation;

    /**
     * @var string
     */
    public string $countExposedInStores;

    /**
     * @var \App\Model\Product\Availability\ProductStockAvailabilityInformation[]
     */
    public array $stocksAvailabilitiesInformation;

    /**
     * @var \App\Model\Category\Listed\CategoryView[]
     */
    public array $categoryViews;

    /**
     * @var \App\Model\Product\Detail\ProductFileView[]
     */
    public array $productFileViews;

    /**
     * @var string
     */
    public string $mainCategoryPath;
}
