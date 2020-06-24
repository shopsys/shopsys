<?php

declare(strict_types=1);

namespace App\Twig;

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
            'getProductDimensionParameterValues',
            [$this, 'getProductDimensionParameterValues']
        );

        $functions[] = new TwigFunction(
            'getProductNonDimensionParameterValues',
            [$this, 'getProductNonDimensionParameterValues']
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductDimensionParameterValues(Product $product): array
    {
        $productParameterValues = $this->getProductParameterValues($product);
        $productDimensionParameterValues = [];

        foreach ($productParameterValues as $parameterValue) {

            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterValue->getParameter();
            if ($parameter->getGroup() !== null && $parameter->getGroup()->getAkeneoCode() === 'param__dimensions') {
                $productDimensionParameterValues[$parameter->getAkeneoCode()] = $parameterValue;
            }
        }

        return $productDimensionParameterValues;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductNonDimensionParameterValues(Product $product): array
    {
        $productParameterValues = $this->getProductParameterValues($product);
        $productNonDimensionParameterValues = [];

        foreach ($productParameterValues as $parameterValue) {

            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterValue->getParameter();
            if ($parameter->getGroup() !== null && $parameter->getGroup()->getAkeneoCode() === 'param__dimensions') {
                continue;
            }
            $productNonDimensionParameterValues[$parameter->getAkeneoCode()] = $parameterValue;
        }

        return $productNonDimensionParameterValues;
    }
}
