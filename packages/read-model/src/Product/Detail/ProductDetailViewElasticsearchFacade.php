<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;

class ProductDetailViewElasticsearchFacade implements ProductDetailViewFacadeInterface
{
    /**
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        protected readonly ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView
    {
        return $this->productDetailViewElasticsearchFactory->createFromProductArray(
            $this->productElasticsearchProvider->getVisibleProductArrayById($productId)
        );
    }
}
