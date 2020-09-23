<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Brand;

interface BrandViewFacadeInterface
{
    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Brand\BrandView|null
     */
    public function findByProductId(int $productId): ?BrandView;
}
