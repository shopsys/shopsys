<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentAndTransportRemovalOnEmptyCartTest extends GraphQlTestCase
{
    public function testRemoveLastItemFromCart(): void
    {
        $cart = $this->addProductToCart();

        self::assertCount(1, $cart['items']);

        $cartUuid = $cart['uuid'];

        $this->addPaymentToCart($cartUuid);
        $this->addTransportToCart($cartUuid);

        $updatedCart = $this->getCart($cartUuid);

        self::assertNotNull($updatedCart['payment']['uuid']);
        self::assertNotNull($updatedCart['transport']['uuid']);

        $emptyCart = $this->removeItemFromCart($cartUuid, $cart['items'][0]['uuid']);

        self::assertEmpty($emptyCart['items']);
        self::assertNull($emptyCart['payment']);
        self::assertNull($emptyCart['transport']);

        $cart = $this->addProductToCart();

        self::assertNull($cart['payment']);
        self::assertNull($cart['transport']);
    }

    /**
     * @return array{
     *     uuid: string,
     *     items: array<int, array{uuid: string}>,
     *     transport: array{uuid: string}|null,
     *     payment: array{uuid: string}|null
     * }
     */
    private function addProductToCart(): array
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $addToCartMutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $product->getUuid() . '", 
                    quantity: 1
                }
            ) {
                cart {
                    uuid
                    items {
                        uuid
                    }
                    payment {
                        uuid
                    }
                    transport {
                        uuid
                    }
                }
            }
        }';

        return $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($addToCartMutation),
            'AddToCart',
        )['cart'];
    }

    /**
     * @param string $cartUuid
     */
    private function addPaymentToCart(string $cartUuid): void
    {
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);

        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    paymentUuid: "' . $payment->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changePaymentInCartMutation);
    }

    /**
     * @param string $cartUuid
     */
    private function addTransportToCart(string $cartUuid): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);

        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    transportUuid: "' . $transport->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    /**
     * @param string $cartUuid
     * @return array{
     *     transport: array{uuid: string}|null,
     *     payment: array{uuid: string}|null
     * }
     */
    private function getCart(string $cartUuid): array
    {
        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $cartUuid . '"}) {
                transport {
                    uuid
                }
                payment {
                    uuid
                }
            }
        }';

        return $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($getCartQuery),
            'cart',
        );
    }

    /**
     * @param string $cartUuid
     * @param string $cartItemUuid
     * @return array{
     *     items: array<int, array{uuid: string}>,
     *     transport: array{uuid: string}|null,
     *     payment: array{uuid: string}|null
     * }
     */
    private function removeItemFromCart(string $cartUuid, string $cartItemUuid): array
    {
        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $cartUuid . '", 
                cartItemUuid: "' . $cartItemUuid . '"
            }) {
                items {
                    uuid
                }
                payment {
                    uuid
                }
                transport {
                    uuid
                }
            }
        }';

        return $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($removeFromCartMutation),
            'RemoveFromCart',
        );
    }
}
