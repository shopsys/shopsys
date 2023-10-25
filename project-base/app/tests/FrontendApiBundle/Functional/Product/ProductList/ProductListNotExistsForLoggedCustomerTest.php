<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ProductListNotExistsForLoggedCustomerTest extends GraphQlWithLoginTestCase
{
    public const DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const DEFAULT_USER_PASSWORD = 'no-reply.3';

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testFindProductListForCustomerUserWithoutProductListReturnsNull(
        ProductListTypeEnum $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'type' => $productListType->name,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testGetProductListsForCustomerUserWithoutProductListReturnsEmptyArray(
        ProductListTypeEnum $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListsByTypeQuery.graphql', [
            'type' => $productListType->name,
        ]);

        $this->assertEmpty($response['data']['productListsByType']);
    }
}
