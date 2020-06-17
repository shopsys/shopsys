<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Product;
use App\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\ProductCachedAttributesFacade
     */
    private $productCachedAttributesFacade;

    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForListFacade
     */
    private $productListOrderingModeForListFacade;

    /**
     * @param \App\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     */
    public function __construct(
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
    ) {
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
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
}
