<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

interface ProductDetailViewFacadeInterface
{
    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView;
}
