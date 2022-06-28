<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Payment\Payment
     */
    protected Payment $payment;

    protected function setUp(): void
    {
        $this->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);

        parent::setUp();
    }

    public function testPaymentNameByUuid(): void
    {
        $query = '
            query {
                payment(uuid: "' . $this->payment->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'payment' => [
                    'name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetFreePayment(): void
    {
        $cartUuid = CartDataFixture::CART_UUID;
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $mutation = 'mutation {
            AddToCart(input: {
                cartUuid:"' . $cartUuid . '"
                productUuid: "' . $product->getUuid() . '"
                quantity: 100
            }) {
                cart {
                    uuid
                }                
            }
        }';

        $this->getResponseContentForQuery($mutation);
        $query = '
            query {
                payment(uuid: "' . $this->payment->getUuid() . '") {
                    price(cartUuid: "' . $cartUuid . '") {
                        priceWithVat
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'payment' => [
                    'price' => [
                        'priceWithVat' => '0.000000',
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
