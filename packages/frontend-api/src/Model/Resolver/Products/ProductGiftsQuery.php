<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductGiftsQuery extends AbstractQuery
{
    /**
     * @param \Overblog\DataLoader\DataLoaderInterface $productGiftsByMainProductIdsBatchLoader
     */
    public function __construct(
        protected readonly DataLoaderInterface $productGiftsByMainProductIdsBatchLoader,
    ) {
    }

    /**
     * @param array|\Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function giftsByProductPromiseQuery(array|Product $product): Promise
    {
        if ($product instanceof Product) {
            $productId = $product->getId();
        } else {
            $productId = $product['id'];
        }

        return $this->productGiftsByMainProductIdsBatchLoader->load($productId);
    }
}
