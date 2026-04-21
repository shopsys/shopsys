<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\ProductInOrder;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductInOrderValidationTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testOrderWithoutProductCannotBeCreated(): void
    {
        $cartUuid = $this->addProductToCartAndRemoveIt();
        $response = $this->getCreateOrderMutationResponseFromCart($cartUuid);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        /** @var array<int, array{message: string, code: string}> $validationErrors */
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response)['input'];

        $errorCodes = array_map(static fn (array $validationError) => $validationError['code'], $validationErrors);
        self::assertContainsEquals(ProductInOrder::NO_PRODUCT_IN_ORDER_ERROR, $errorCodes);
    }

    public function testOrderWithRemovedProductsByAdmin(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '77', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $this->productFacade->delete($product->getId());

        $response = $this->getCreateOrderMutationResponseFromCart($cartUuid);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('someProductWasRemovedFromEshop', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['someProductWasRemovedFromEshop']);
    }

    private function addProductToCartAndRemoveIt(): string
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $addToCartMutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $product->getUuid() . '",
                quantity: 1
            }) {
                cart {
                    uuid
                    items {
                        uuid
                    }
                }
            }
        }';

        $cart = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($addToCartMutation),
            'AddToCart',
        )['cart'];
        $cartUuid = $cart['uuid'];
        $cartItemUuid = $cart['items'][0]['uuid'];

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $cartUuid . '",
                cartItemUuid: "' . $cartItemUuid . '"
            }) {
                uuid
            }
        }';

        $this->getResponseContentForQuery($removeFromCartMutation);

        return $cartUuid;
    }
}
