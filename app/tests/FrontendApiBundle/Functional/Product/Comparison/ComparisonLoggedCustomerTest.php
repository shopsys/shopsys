<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Comparison;

use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Mutation\Product\Comparison\ComparisonMutation;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ComparisonLoggedCustomerTest extends GraphQlWithLoginTestCase
{
    public function testGetNotExistsComparison(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql');

        $this->assertNull($response['data']['comparison']);
    }

    public function testAddProductToComparison(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product->getUuid()]);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('addProductToComparison', $response['data']);
        $this->assertArrayHasKey('products', $response['data']['addProductToComparison']);
        $this->assertArrayHasKey(0, $response['data']['addProductToComparison']['products']);
        $this->assertArrayHasKey('name', $response['data']['addProductToComparison']['products'][0]);
        $this->assertSame('22" Sencor SLE 22F46DM4 HELLO KITTY', $response['data']['addProductToComparison']['products'][0]['name']);
    }

    public function testGetComparisonOfLoggedCustomer(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $responseExpected = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql');

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('comparison', $response['data']);
        $this->assertSame($responseExpected['data']['addProductToComparison'], $response['data']['comparison']);
    }

    public function testAddSameProductToComparison(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product->getUuid()]);

        $this->assertSame('Product 22" Sencor SLE 22F46DM4 HELLO KITTY in comparison already exists.', $response['errors'][0]['message']);
    }

    public function testRemoveProductFromComparison(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product2->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromComparison.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertSame(1, count($response['data']['removeProductFromComparison']['products'][0]));
        $this->assertSame('32" Philips 32PFL4308', $response['data']['removeProductFromComparison']['products'][0]['name']);
    }

    public function testRemoveLastProductFromComparison(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromComparison.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertNull($response['data']['removeProductFromComparison']);
    }

    public function testCleanComparison(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CleanComparisonMutation.graphql');

        $this->assertSame(ComparisonMutation::SUCCESS_RESULT, $response['data']['cleanComparison']);
    }

    public function testAddProductsAsNotLoggedCustomer(): void
    {
        $this->logout();
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertArrayHasKey('data', $response1);
        $this->assertArrayHasKey('addProductToComparison', $response1['data']);
        $this->assertArrayHasKey('uuid', $response1['data']['addProductToComparison']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToComparison']);
        $this->assertSame(1, count($response1['data']['addProductToComparison']['products']));
        $comparisonUuid = $response1['data']['addProductToComparison']['uuid'];

        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product2->getUuid(), 'comparisonUuid' => $comparisonUuid]);

        $this->assertArrayHasKey('data', $response2);
        $this->assertArrayHasKey('addProductToComparison', $response2['data']);
        $this->assertArrayHasKey('uuid', $response2['data']['addProductToComparison']);
        $this->assertArrayHasKey('products', $response2['data']['addProductToComparison']);
        $this->assertSame(2, count($response2['data']['addProductToComparison']['products']));
        $this->assertSame($comparisonUuid, $response2['data']['addProductToComparison']['uuid']);
    }
}
