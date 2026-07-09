<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CreateOrderCurrencyTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testOrderIsCreatedInCurrencyFromHeaderWithExchangeRateSnapshot(): void
    {
        $this->configureCurrentClient(null, null, [
            'CONTENT_TYPE' => 'application/graphql',
            'HTTP_X_CURRENCY_CODE' => 'CZK',
        ]);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);
        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateOrderCurrencyMutation.graphql', [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => new PhoneData('CU', '+53', '123456789'),
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'isDeliveryAddressDifferentFromBilling' => false,
        ]);

        $createOrderData = $this->getResponseDataForGraphQlType($response, 'CreateOrder');

        self::assertTrue($createOrderData['orderCreated']);
        self::assertSame('CZK', $createOrderData['order']['currencyCode']);
        self::assertSame('CZK', $createOrderData['order']['totalPrice']['currencyCode']);

        $order = $this->orderFacade->getByUuid($createOrderData['order']['uuid']);

        self::assertSame('CZK', $order->getCurrencyCode());
        self::assertSame('0.040000', $order->getCurrencyExchangeRate());
        self::assertSame($order->getCurrencyExchangeRate(), $createOrderData['order']['exchangeRate']);
    }
}
