<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ProductDetailViewFacade implements ProductDetailViewFacadeInterface
{
    /**
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory $productDetailViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     */
    public function __construct(
        protected readonly ProductDetailViewFactory $productDetailViewFactory,
        protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView
    {
        $product = $this->productOnCurrentDomainFacade->getVisibleProductById($productId);

        return $this->productDetailViewFactory->createFromProduct($product);
    }
}
