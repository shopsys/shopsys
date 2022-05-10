<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Model\Component\Constraints\ProductInOrder;

class ProductInOrderValidationTest extends AbstractOrderTestCase
{
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
            'AddToCart'
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
     * @return array
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
                        uuid
                    }
                }';

        return $this->getResponseContentForQuery($mutation);
    }
}
