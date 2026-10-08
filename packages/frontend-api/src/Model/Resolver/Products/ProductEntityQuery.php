<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

/**
 * Product types are always resolved from Elasticsearch data, so fields returning product entities
 * (e.g. OrderItem.product) resolve them by this query. Products that are not visible for the current customer
 * are not in Elasticsearch, so they become null (single value) or are omitted (list).
 */
class ProductEntityQuery extends AbstractQuery
{
    public function __construct(
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
    ) {
    }

    public function productByEntityQuery(?Product $product): ?Promise
    {
        if ($product === null) {
            return null;
        }

        return $this->productsVisibleByIdsBatchLoader->load([$product->getId()])
            ->then(static fn (array $products) => $products[0] ?? null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function productsByEntitiesQuery(array $products): Promise
    {
        return $this->productsVisibleByIdsBatchLoader->load(
            array_map(static fn (Product $product) => $product->getId(), $products),
        );
    }
}
