<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Parameter\ParameterGroup;
use App\Model\Product\Parameter\ParameterValuesViewData;
use App\Model\Product\Product;
use App\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Twig\ProductExtension as BaseProductExtension;
use Twig\TwigFunction;

/**
 * Class ProductExtension
 * @property \App\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
 */
class ProductExtension extends BaseProductExtension
{
    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForListFacade
     */
    private $productListOrderingModeForListFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
    ) {
        parent::__construct(
            $categoryFacade,
            $productCachedAttributesFacade
        );
        $this->productListOrderingModeForListFacade = $productListOrderingModeForListFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions[] = new TwigFunction(
            'getProductNonSellingPrice',
            [$this, 'getProductNonSellingPrice']
        );
        $functions[] = new TwigFunction(
            'getOrderingNameByOrderingId',
            [$this, 'getOrderingNameByOrderingId']
        );

        $functions[] = new TwigFunction(
            'getVariantKeyByParameterValues',
            [$this, 'getVariantKeyByParameterValues']
        );

        $functions[] = new TwigFunction(
            'getProductDimensionParameterValuesViewsData',
            [$this, 'getProductDimensionParameterValuesViewsData']
        );

        $functions[] = new TwigFunction(
            'getProductNonDimensionParameterValuesViewsData',
            [$this, 'getProductNonDimensionParameterValuesViewsData']
        );

        return $functions;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getProductNonSellingPrice(Product $product): ProductPrice
    {
        return $this->productCachedAttributesFacade->getProductNonSellingPrice($product);
    }

    /**
     * @param string $orderingId
     * @return string
     */
    public function getOrderingNameByOrderingId(?string $orderingId): string
    {
        if ($orderingId === null) {
            return '';
        }

        $supportedOrderingModesNamesIndexedById = $this->productListOrderingModeForListFacade
            ->getProductListOrderingConfig()
            ->getSupportedOrderingModesNamesIndexedById();

        return $supportedOrderingModesNamesIndexedById[$orderingId] ?? t('Neplatné řazení') . ' ' . $orderingId;
    }

    /**
     * @param array $parameterValueIdsIndexedByParameterId
     * @param string $parameterId
     * @param string $parameterValueId
     * @return string
     */
    public function getVariantKeyByParameterValues(array $parameterValueIdsIndexedByParameterId, string $parameterId, string $parameterValueId): string
    {
        $parameterValueIdsIndexedByParameterId[$parameterId] = $parameterValueId;
        $variantSetupParts = [];
        foreach ($parameterValueIdsIndexedByParameterId as $parameterId => $parameterValueId) {
            $variantSetupParts[] = $parameterId . '_' . $parameterValueId;
        }

        return implode('~', $variantSetupParts);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getProductDimensionParameterValuesViewsData(Product $product): array
    {
        $parameterValuesViewsData = $this->productCachedAttributesFacade->getParameterValuesViewsData($product);

        return array_filter(
            $parameterValuesViewsData,
            static function (ParameterValuesViewData $parameterValuesViewData): bool {
                return $parameterValuesViewData->getParameterGroupAkeneoCode() === ParameterGroup::AKENEO_CODE_DIMENSIONS;
            }
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getProductNonDimensionParameterValuesViewsData(Product $product): array
    {
        $parameterValuesViewsData = $this->productCachedAttributesFacade->getParameterValuesViewsData($product);

        return array_filter(
            $parameterValuesViewsData,
            static function (ParameterValuesViewData $parameterValuesViewData): bool {
                return $parameterValuesViewData->getParameterGroupAkeneoCode() !== ParameterGroup::AKENEO_CODE_DIMENSIONS;
            }
        );
    }
}
