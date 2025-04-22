<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithPromoCodeTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private NumberFormatterExtension $numberFormatterExtension;

    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    private const array DEFAULT_ORDER_INPUT_VALUES = [
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'email' => 'user@example.com',
        'telephone' => '+53 123456789',
        'onCompanyBehalf' => false,
        'street' => '123 Fake Street',
        'city' => 'Springfield',
        'postcode' => '12345',
        'country' => 'CZ',
        'isDeliveryAddressDifferentFromBilling' => false,
    ];

    public function testCreateOrderWithPromoCode(): void
    {
        $expectedOrderItems = $this->getExpectedOrderItems();
        $cartUuid = $this->addProductToCart();
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $this->applyPromoCode($cartUuid, $validPromoCode->getCode());

        $responseData = $this->createOrderAndGetResponseData($cartUuid);

        $this->assertTrue($responseData['orderCreated']);
        $this->assertSame(self::getSerializedOrderTotalPriceByExpectedOrderItems($expectedOrderItems), $responseData['order']['totalPrice']);
        $this->assertSame($expectedOrderItems, $responseData['order']['items']);
        $this->assertNull($responseData['cart']);
    }

    public function testCreateOrderWithInvalidPromoCode(): void
    {
        $cartUuid = $this->addProductToCart();

        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $this->applyPromoCode($cartUuid, $validPromoCode->getCode());
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;

        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $responseData = $this->createOrderAndGetResponseData($cartUuid);

        $this->assertArrayHasKey('orderCreated', $responseData);
        $this->assertFalse($responseData['orderCreated']);
        $this->assertArrayHasKey('cart', $responseData);
        $this->assertArrayHasKey('promoCodes', $responseData['cart']);
        $this->assertCount(0, $responseData['cart']['promoCodes']);
        $this->assertArrayHasKey('modifications', $responseData['cart']);
        $this->assertArrayHasKey('promoCodeModifications', $responseData['cart']['modifications']);
        $this->assertArrayHasKey('noLongerApplicablePromoCode', $responseData['cart']['modifications']['promoCodeModifications']);
        $this->assertCount(1, $responseData['cart']['modifications']['promoCodeModifications']['noLongerApplicablePromoCode']);
        $this->assertEquals('test', $responseData['cart']['modifications']['promoCodeModifications']['noLongerApplicablePromoCode'][0]);
    }

    public function testOrderWithFreeTransportAndPaymentPromoCode(): void
    {
        $cartUuid = $this->addProductToCart();
        $freeTransportAndPaymentPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::PROMO_CODE_FOR_FREE_TRANSPORT_PAYMENT, 1, PromoCode::class);
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);
        $this->applyPromoCode($cartUuid, $freeTransportAndPaymentPromoCode->getCode());

        $responseData = $this->createOrderAndGetResponseData($cartUuid);

        $this->assertTransportAndPaymentItemsAreFree($responseData);
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $domainId = $this->domain->getId();
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId, Vat::class);

        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        return [
            0 => [
                'name' => $helloKittyProduct->getFullName($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => OrderItemTypeEnum::TYPE_PRODUCT,
                'product' => [
                    'uuid' => $helloKittyProduct->getUuid(),
                ],
            ],
            1 => [
                'name' => $this->getExpectedPromoCodeItemName($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('-289.26', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('-289.26', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_DISCOUNT,
                'product' => null,
            ],
            2 => [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'quantity' => 1,
                'vatRate' => $vatZero->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_PAYMENT,
                'product' => null,
            ],
            3 => [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_TRANSPORT,
                'product' => null,
            ],
        ];
    }

    /**
     * @param string $cartUuid
     * @return array
     */
    private function createOrderAndGetResponseData(string $cartUuid): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            'cartUuid' => $cartUuid,
            ...self::DEFAULT_ORDER_INPUT_VALUES,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'CreateOrder');
    }

    /**
     * @param string $cartUuid
     * @param string $promoCode
     */
    public function applyPromoCode(string $cartUuid, string $promoCode): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $promoCode,
        ]);
    }

    /**
     * @param string $firstDomainLocale
     * @return string
     */
    private function getExpectedPromoCodeItemName(string $firstDomainLocale): string
    {
        return sprintf(
            '%s %s - %s %s %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
            $this->numberFormatterExtension->formatPercent('-10', $firstDomainLocale),
            t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );
    }

    /**
     * @return string
     */
    private function addProductToCart(): string
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        return $response['data']['AddToCart']['cart']['uuid'];
    }

    /**
     * @param array $responseData
     */
    private function assertTransportAndPaymentItemsAreFree(array $responseData): void
    {
        foreach ($responseData['order']['items'] as $item) {
            if ($item['type'] === OrderItemTypeEnum::TYPE_TRANSPORT || $item['type'] === OrderItemTypeEnum::TYPE_PAYMENT) {
                $this->assertSame(
                    $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('0'),
                    $item['totalPrice']['priceWithVat'],
                    sprintf('Total price of %s should be zero', $item['type']),
                );
            }
        }
    }
}
