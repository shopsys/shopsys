<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTotalItemsPriceTest extends GraphQlTestCase
{
    public function testCartTotalItemsPriceDoesNotIncludeTransportAndPaymentPrice(): void
    {
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, 1, Vat::class);

        $cartUuid = $this->getResponseDataForGraphQlType($this->addHelloKittyToNewCart(), 'AddToCart')['cart']['uuid'];
        $this->addPaymentCardToCart($cartUuid);
        $this->addTransportPplToCart($cartUuid);

        $response = $this->getCartResponse($cartUuid);
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');
        $expectedPrice = $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh);

        $this->assertSame($expectedPrice, $responseData['totalItemsPrice']);
    }

    public function testRemainingItemsAmountToPayExcludesTransportAndPayment(): void
    {
        $cartUuid = $this->getResponseDataForGraphQlType($this->addHelloKittyToNewCart(), 'AddToCart')['cart']['uuid'];
        $this->addPaymentCardToCart($cartUuid);
        $this->addTransportPplToCart($cartUuid);

        $responseData = $this->getResponseDataForGraphQlType($this->getCartPricesResponse($cartUuid), 'cart');

        $totalPriceWithVat = Money::create($responseData['totalPrice']['priceWithVat']);
        $totalItemsPriceWithVat = Money::create($responseData['totalItemsPrice']['priceWithVat']);

        $this->assertTrue(
            $totalPriceWithVat->isGreaterThan($totalItemsPriceWithVat),
            'The selected transport and payment must make the total larger than the items price.',
        );
        $this->assertTrue($totalItemsPriceWithVat->equals(Money::create($responseData['remainingItemsAmountToPay'])));
        $this->assertTrue($totalPriceWithVat->equals(Money::create($responseData['remainingAmountToPay'])));
    }

    /**
     * @return array<string, mixed>
     */
    private function getCartPricesResponse(string $cartUuid): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/CartPricesQuery.graphql', [
            'cartUuid' => $cartUuid,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function addHelloKittyToNewCart(): array
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $helloKittyProduct->getUuid(),
            'quantity' => 1,
        ]);
    }

    private function addPaymentCardToCart(string $cartUuid): void
    {
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'paymentUuid' => $paymentCard->getUuid(),
        ]);
    }

    private function addTransportPplToCart(string $cartUuid): void
    {
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'transportUuid' => $transportPpl->getUuid(),
        ]);
    }

    private function getCartResponse(string $cartUuid): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/CartWithDiscountBreakdown.graphql', [
            'cartUuid' => $cartUuid,
        ]);
    }
}
