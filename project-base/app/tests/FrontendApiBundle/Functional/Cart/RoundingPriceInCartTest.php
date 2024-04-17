<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RoundingPriceInCartTest extends GraphQlTestCase
{
    private Product $testingProduct;

    /**
     * @inject
     */
    private CurrencyDataFactory $currencyDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testRoundingPriceIsNullForFirstDomain(): void
    {
        if ($this->getFirstDomainCurrency()->getCode() === 'CZK') {
            $this->markTestSkipped('This test is not relevant for CZK currency');
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => 1,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        $cartUuid = $data['cart']['uuid'];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => $cartUuid,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        $this->assertNull($data['roundingPrice'], 'Rounding price has to be null for first domain');

        $this->assertEquals(
            $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('3498.96'),
            $data['totalPrice']['priceWithVat'],
        );
    }

    public function testProperRoundingIsReturnedForCashPaymentInCzk(): void
    {
        $this->setCurrencyOnFirstDomainToCzkWithoutRounding();
        $cartUuid = $this->createCartWithProductTransportAndPayment();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => $cartUuid,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        $this->assertNotNull($data['roundingPrice'], 'Rounding price has to be set for cash payment in CZK');

        // domain is switched to CZK currency, so all following prices are different from DataFixtures

        $expectedRoundingAmount = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('0.04');
        $this->assertEquals($data['roundingPrice']['priceWithoutVat'], $expectedRoundingAmount);
        $this->assertEquals($data['roundingPrice']['priceWithVat'], $expectedRoundingAmount);

        $this->assertEquals(
            $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('140'),
            $data['totalPrice']['priceWithVat'],
        );
    }

    public function setCurrencyOnFirstDomainToCzkWithoutRounding(): void
    {
        $currencyCzk = $this->currencyFacade->getByCode('CZK');

        $currencyData = $this->currencyDataFactory->createFromCurrency($currencyCzk);
        $currencyData->roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS;
        $currencyCzk = $this->currencyFacade->edit($currencyCzk->getId(), $currencyData);

        $this->currencyFacade->setDomainDefaultCurrency($currencyCzk, Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @return string
     */
    public function createCartWithProductTransportAndPayment(): string
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => 1,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');
        $cartUuid = $data['cart']['uuid'];

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'transportUuid' => $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class)->getUuid(),
            'pickupPlaceIdentifier' => $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'paymentUuid' => $this->getReference(PaymentDataFixture::PAYMENT_CASH, Payment::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangePaymentInCart');

        return $cartUuid;
    }
}
