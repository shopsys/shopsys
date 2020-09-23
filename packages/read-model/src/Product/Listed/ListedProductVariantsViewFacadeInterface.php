<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

interface ListedProductVariantsViewFacadeInterface
{
    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAllVariants(int $productId): array;
}
