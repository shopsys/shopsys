<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

trait ProductListProductIdsTrait
{
    /**
     * @param array{products: array{edges: array<int, array{node: array{id: int}}>}} $productListData
     * @return int[]
     */
    private function getProductIds(array $productListData): array
    {
        return array_column(array_column($productListData['products']['edges'], 'node'), 'id');
    }
}
