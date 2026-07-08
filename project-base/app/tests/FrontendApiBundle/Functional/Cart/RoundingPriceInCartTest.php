<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
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

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    #[Override]
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
            $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('3498.96'),
            $data['totalPrice']['priceWithVat'],
        );
    }

    public function testProperRoundingIsReturnedForCashPaymentWithWholeRounding(): void
    {
        $this->setFirstDomainCurrencyRoundingToHundredthsAndCashPaymentToWholeRounding();
        $cartUuid = $this->createCartWithProductTransportAndPayment();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => $cartUuid,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        $this->assertNotNull($data['roundingPrice'], 'Rounding price has to be set for cash payment with whole rounding');

        // cart total is 3498.96 CZK converted to the domain default currency (139.96 EUR), whole rounding adds 0.04
        $expectedRoundingAmount = $this->moneyFormatterHelper->formatWithMaxFractionDigits(Money::create('0.04'));
        $this->assertEquals($data['roundingPrice']['priceWithoutVat'], $expectedRoundingAmount);
        $this->assertEquals($data['roundingPrice']['priceWithVat'], $expectedRoundingAmount);
    }

    public function setFirstDomainCurrencyRoundingToHundredthsAndCashPaymentToWholeRounding(): void
    {
        $firstDomainCurrency = $this->getFirstDomainCurrency();

        $currencyData = $this->currencyDataFactory->createFromCurrency($firstDomainCurrency);
        $currencyData->roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS;
        $this->currencyFacade->edit($firstDomainCurrency->getId(), $currencyData);

        $cashPayment = $this->getReference(PaymentDataFixture::PAYMENT_CASH, Payment::class);
        $cashPaymentData = $this->paymentDataFactory->createFromPayment($cashPayment);
        $cashPaymentData->orderRoundingTypeByDomainId[Domain::FIRST_DOMAIN_ID] = OrderRoundingTypeEnum::WHOLE;
        $this->paymentFacade->edit($cashPayment, $cashPaymentData);
    }

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
