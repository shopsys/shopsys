<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
                    'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
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

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => 100,
        ]);

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
