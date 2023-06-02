<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Comparison;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Comparison\ComparisonFacade;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ComparisonMergingTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     * @var \App\Model\Product\Comparison\ComparisonFacade
     */
    private ComparisonFacade $comparisonFacade;

    public function testTransformNotLoggedCustomerComparisonToLoggedCustomerComparison(): void
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

        $this->login();
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql', ['comparisonUuid' => $comparisonUuid]);
        $this->assertArrayHasKey('data', $response2);
        $this->assertArrayHasKey('comparison', $response2['data']);
        $this->assertArrayHasKey('uuid', $response2['data']['comparison']);
        $this->assertArrayHasKey('products', $response2['data']['comparison']);
        $this->assertSame(1, count($response2['data']['comparison']['products']));
        $this->assertSame($comparisonUuid, $response2['data']['comparison']['uuid']);
        $this->assertSame($response1['data']['addProductToComparison']['products'], $response2['data']['comparison']['products']);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);
        $loggedUserComparison = $this->comparisonFacade->getComparisonOfCustomerUser($customerUser);
        $this->assertNotNull($loggedUserComparison);
        $this->assertSame($response2['data']['comparison']['products'][0]['name'], $loggedUserComparison->getItems()[0]->getProduct()->getName());
    }

    public function testMergeNotLoggedCustomerComparisonWithLoggedCustomerComparison()
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertArrayHasKey('data', $response1);
        $this->assertArrayHasKey('addProductToComparison', $response1['data']);
        $this->assertArrayHasKey('uuid', $response1['data']['addProductToComparison']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToComparison']);
        $this->assertSame(1, count($response1['data']['addProductToComparison']['products']));
        $comparisonUuidOfLoggedUser = $response1['data']['addProductToComparison']['uuid'];

        $this->logout();
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product2->getUuid()]);

        $this->assertArrayHasKey('data', $response2);
        $this->assertArrayHasKey('addProductToComparison', $response2['data']);
        $this->assertArrayHasKey('uuid', $response2['data']['addProductToComparison']);
        $this->assertArrayHasKey('products', $response2['data']['addProductToComparison']);
        $this->assertSame(1, count($response2['data']['addProductToComparison']['products']));
        $this->assertNotSame($comparisonUuidOfLoggedUser, $response2['data']['addProductToComparison']['uuid']);
        $comparisonUuidOfNotLoggedUser = $response2['data']['addProductToComparison']['uuid'];

        $this->login();
        $response3 = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql', ['comparisonUuid' => $comparisonUuidOfNotLoggedUser]);
        $this->assertArrayHasKey('data', $response3);
        $this->assertArrayHasKey('comparison', $response3['data']);
        $this->assertArrayHasKey('uuid', $response3['data']['comparison']);
        $this->assertArrayHasKey('products', $response3['data']['comparison']);
        $this->assertSame(2, count($response3['data']['comparison']['products']));
        $this->assertSame($comparisonUuidOfLoggedUser, $response3['data']['comparison']['uuid']);

        $this->logout();
        $response4 = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql', ['comparisonUuid' => $comparisonUuidOfNotLoggedUser]);
        $this->assertArrayHasKey('data', $response4);
        $this->assertArrayHasKey('comparison', $response4['data']);
        $this->assertNull($response4['data']['comparison']);
    }

    public function testLogoutCustomerWithComparison()
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToComparisonMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertArrayHasKey('data', $response1);
        $this->assertArrayHasKey('addProductToComparison', $response1['data']);
        $this->assertArrayHasKey('uuid', $response1['data']['addProductToComparison']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToComparison']);
        $this->assertSame(1, count($response1['data']['addProductToComparison']['products']));

        $this->logout();
        $response4 = $this->getResponseContentForGql(__DIR__ . '/graphql/ComparisonQuery.graphql');
        $this->assertArrayHasKey('data', $response4);
        $this->assertArrayHasKey('comparison', $response4['data']);
        $this->assertNull($response4['data']['comparison']);
    }
}
