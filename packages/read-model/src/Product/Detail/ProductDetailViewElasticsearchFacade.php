<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;

class ProductDetailViewElasticsearchFacade implements ProductDetailViewFacadeInterface
{
    protected ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory;

    protected ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory,
        ProductElasticsearchProvider $productElasticsearchProvider
    ) {
        $this->productDetailViewElasticsearchFactory = $productDetailViewElasticsearchFactory;
        $this->productElasticsearchProvider = $productElasticsearchProvider;
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
