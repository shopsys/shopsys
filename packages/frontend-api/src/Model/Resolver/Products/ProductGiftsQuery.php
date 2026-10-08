<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductGiftsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly DataLoaderInterface $productGiftsByMainProductIdsBatchLoader,
    ) {
    }

    public function giftsByProductPromiseQuery(array $product): Promise
    {
        return $this->productGiftsByMainProductIdsBatchLoader->load($product['id']);
    }
}
