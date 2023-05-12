<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ParameterViewFacade implements ParameterViewFacadeInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory $parameterViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ParameterViewFactory $parameterViewFactory,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
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
