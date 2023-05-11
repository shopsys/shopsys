<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ParameterViewFacade implements ParameterViewFacadeInterface
{
    protected ProductFacade $productFacade;

    protected ParameterViewFactory $parameterViewFactory;

    protected ProductCachedAttributesFacade $productCachedAttributesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory $parameterViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ParameterViewFactory $parameterViewFactory,
        ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
        $this->parameterViewFactory = $parameterViewFactory;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->productFacade = $productFacade;
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView[]
     */
    public function getAllByProductId(int $productId): array
    {
        $product = $this->productFacade->getById($productId);

        $productParameterValues = $this->productCachedAttributesFacade->getProductParameterValues($product);

        $parameterViews = [];

        foreach ($productParameterValues as $productParameterValue) {
            $parameterViews[] = $this->parameterViewFactory->createFromProductParameterValue($productParameterValue);
        }

        return $parameterViews;
    }
}
