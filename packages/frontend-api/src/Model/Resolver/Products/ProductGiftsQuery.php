<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductGiftsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly DataLoaderInterface $productGiftsByMainProductIdsBatchLoader,
    ) {
    }

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
