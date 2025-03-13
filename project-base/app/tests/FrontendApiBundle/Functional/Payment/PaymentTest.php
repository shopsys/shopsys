<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    protected Payment $payment;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class);
    }

    public function testPaymentNameByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentQuery.graphql', [
            'uuid' => $this->payment->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'payment');

        $this->assertSame(t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $data['name']);
    }

    public function testGetFreePayment(): void
    {
        $cartUuid = CartDataFixture::CART_UUID;
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => 100,
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentQuery.graphql', [
            'uuid' => $this->payment->getUuid(),
            'cartUuid' => $cartUuid,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'payment');

        $this->assertSame('0.000000', $data['price']['priceWithVat']);
    }
}
