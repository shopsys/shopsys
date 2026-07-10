<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Tests\FrontendApiBundle\Test\PromoCodeAssertionTrait;

class TotalItemsPriceBeforeDiscountTest extends GraphQlTestCase
{
    use PromoCodeAssertionTrait;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    public function testTotalItemsPriceBeforeDiscount(): void
    {
        $testingProductVoucher = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $voucherQuantity = 3;
        $newlyCreatedCart = $this->addTestingProductToCart($testingProductVoucher, $voucherQuantity);

        $testingProductHelloKitty = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $helloKittyQuantity = 5;
        $this->addTestingProductToCart($testingProductHelloKitty, $helloKittyQuantity, $newlyCreatedCart['uuid']);

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);
        self::assertNotNull($cart);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql',
            [
                'cartUuid' => $newlyCreatedCart['uuid'],
                'promoCode' => $promoCode->getCode(),
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyCodeToCart');
        self::assertPromoCode($promoCode, $data['promoCodes'][0]);

        $testingTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $pickupPlaceIdentifier = $store->getUuid();
        $this->addTransportToCart($newlyCreatedCart, $testingTransport, $pickupPlaceIdentifier);

        $testingPayment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->addPaymentToCart($newlyCreatedCart, $testingPayment);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/TotalItemsPriceBeforeDiscountQuery.graphql',
            [
                'cartUuid' => $newlyCreatedCart['uuid'],
            ],
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');

        $expectedPriceWithVat = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create('17858'),
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class),
            Domain::FIRST_DOMAIN_ID,
        );

        self::assertThat(
            Money::create($responseData['totalItemsPriceBeforeDiscount']['priceWithVat']),
            new IsMoneyEqual($expectedPriceWithVat),
        );
    }

    private function addTestingProductToCart(Product $product, int $productQuantity, ?string $cartUuid = null): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
    }

    private function addTransportToCart(array $cart, Transport $transport, string $pickupPlaceIdentifier): array
    {
        return $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql',
            [
                'cartUuid' => $cart['uuid'],
                'transportUuid' => $transport->getUuid(),
                'pickupPlaceIdentifier' => $pickupPlaceIdentifier,
            ],
        );
    }

    private function addPaymentToCart(array $cart, Payment $payment): array
    {
        return $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql',
            [
                'cartUuid' => $cart['uuid'],
                'paymentUuid' => $payment->getUuid(),
            ],
        );
    }
}
