<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Model\Component\Constraints\ProductInOrder;
use App\Model\Product\ProductFacade;
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
        $response = $this->createOrder($cartUuid);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        /** @var array<int, array{message: string, code: string}> $validationErrors */
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response)['input'];

        $errorCodes = array_map(static fn (array $validationError) => $validationError['code'], $validationErrors);
        self::assertContainsEquals(ProductInOrder::NO_PRODUCT_IN_ORDER_ERROR, $errorCodes);
    }

    public function testOrderWithRemovedProductsByAdmin(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '77');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $this->productFacade->delete($product->getId());

        $response = $this->createOrder($cartUuid);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('someProductWasRemovedFromEshop', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['someProductWasRemovedFromEshop']);
    }

    /**
     * @return string
     */
    private function addProductToCartAndRemoveIt(): string
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
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

    /**
     * @param string $cartUuid
     * @return mixed[]
     */
    private function createOrder(string $cartUuid): array
    {
        $mutation = 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . $cartUuid . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            differentDeliveryAddress: false
                        }
                    ) {
                        order {
                            uuid
                        }
                        cart {
                            modifications {
                                someProductWasRemovedFromEshop
                            }
                        }
                    }
                }';

        return $this->getResponseContentForQuery($mutation);
    }
}
