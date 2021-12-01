<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\FrontendApi\Model\Component\Constraints\PromoCode;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class OrderWithPromoCodeTest extends AbstractOrderTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension
     * @inject
     */
    private NumberFormatterExtension $numberFormatterExtension;

    public function testCreateOrderWithPromoCode()
    {
        $expectedOrderItems = $this->getExpectedOrderItems();
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'totalPrice' => AbstractOrderTestCase::getSerializedOrderTotalPriceByExpectedOrderItems(
                        $expectedOrderItems
                    ),
                    'items' => $expectedOrderItems,
                ],
            ],
        ];
        $cartUuid = $this->addProductToCart();

        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);
        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid, $validPromoCode->getCode()), $expected);
    }

    /**
     * @dataProvider getInvalidPromoCodesDataProvider
     * @param string|null $promoCodeReferenceName
     * @param string $expectedError
     */
    public function testCreateOrderWithInvalidPromoCode(?string $promoCodeReferenceName, string $expectedError)
    {
        $cartUuid = $this->addProductToCart();

        $promoCodeCode = 'non-existing-promo-code';
        if ($promoCodeReferenceName !== null) {
            /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
            $promoCode = $this->getReferenceForDomain($promoCodeReferenceName, 1);
            $promoCodeCode = $promoCode->getCode();
        }

        $mutation = $this->getMutation($cartUuid, $promoCodeCode);
        $response = $this->getResponseContentForQuery($mutation);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals($expectedError, $violations['input.promoCode'][0]['code']);
    }

    /**
     * @return array
     */
    public function getInvalidPromoCodesDataProvider(): array
    {
        return [
            [null, PromoCode::INVALID_ERROR],
            [PromoCodeDataFixture::PROMO_CODE_FOR_PRODUCT_ID_2, PromoCode::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR],
            [PromoCodeDataFixture::NOT_YET_VALID_PROMO_CODE, PromoCode::NOT_YET_VALID_ERROR],
            [PromoCodeDataFixture::NO_LONGER_VALID_PROMO_CODE, PromoCode::NO_LONGER_VALID_ERROR],
            [PromoCodeDataFixture::PROMO_CODE_FOR_REGISTERED_ONLY, PromoCode::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR],
            [PromoCodeDataFixture::PROMO_CODE_FOR_VIP_PRICING_GROUP, PromoCode::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR],
        ];
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        return [
            0 => [
                'name' => t('Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh),
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            1 => [
                'name' => $this->getExpectedPromoCodeItemName($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('-289.26', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('-289.26', $vatHigh),
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
            2 => [
                'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            3 => [
                'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
        ];
    }

    /**
     * @param string $cartUuid
     * @param string $promoCodeCode
     * @return string
     */
    private function getMutation(string $cartUuid, string $promoCodeCode): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('50', $vatZero);

        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transportCzechPost */
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $transportPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatHigh);

        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . $cartUuid . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            payment: {
                                uuid: "' . $paymentCashOnDelivery->getUuid() . '"
                                price: ' . $paymentPrice . '
                            }
                            transport: {
                                uuid: "' . $transportCzechPost->getUuid() . '"
                                price: ' . $transportPrice . '
                            }
                            differentDeliveryAddress: false
                            promoCode: "' . $promoCodeCode . '"
                        }
                    ) {                        
                        totalPrice {
                            priceWithVat
                            priceWithoutVat
                            vatAmount
                        }
                        items {
                            name
                            unitPrice {
                                priceWithVat
                                priceWithoutVat
                                vatAmount
                            }
                            totalPrice {
                                priceWithVat
                                priceWithoutVat
                                vatAmount
                            }
                            quantity
                            vatRate
                            unit
                        }                        
                    }
                }';
    }

    /**
     * @param string $firstDomainLocale
     * @return string
     */
    private function getExpectedPromoCodeItemName(string $firstDomainLocale): string
    {
        return sprintf(
            '%s %s - %s',
            t('Promo code', [], 'messages', $firstDomainLocale),
            $this->numberFormatterExtension->formatPercent(-10, $firstDomainLocale),
            t('Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová', [], 'dataFixtures', $firstDomainLocale)
        );
    }

    /**
     * @return string
     */
    private function addProductToCart(): string
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $product->getUuid() . '",
                quantity: 1
            }) {
                uuid
            }
        }';

        return  $this->getResponseContentForQuery($mutation)['data']['AddToCart']['uuid'];
    }
}
