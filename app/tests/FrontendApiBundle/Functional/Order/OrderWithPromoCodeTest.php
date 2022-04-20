<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\FrontendApi\Model\Component\Constraints\PromoCode;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class OrderWithPromoCodeTest extends AbstractOrderTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension
     * @inject
     */
    private NumberFormatterExtension $numberFormatterExtension;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeDataFactory
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

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
        $this->addCzechPostTransportToCart($cartUuid);

        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $this->applyPromoCode($cartUuid, $validPromoCode->getCode());

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid), $expected);
    }

    public function testCreateOrderWithInvalidPromoCode(): void
    {
        $cartUuid = $this->addProductToCart();

        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $this->applyPromoCode($cartUuid, $validPromoCode->getCode());
        $this->addCzechPostTransportToCart($cartUuid);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;

        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $mutation = $this->getMutation($cartUuid);
        $response = $this->getResponseContentForQuery($mutation);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals(PromoCode::INVALID_ERROR, $violations['input.promoCode'][0]['code']);
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
     * @return string
     */
    private function getMutation(string $cartUuid): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('50', $vatZero);

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
                            differentDeliveryAddress: false
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
     * @param string $cartUuid
     * @param string $promoCode
     */
    public function applyPromoCode(string $cartUuid, string $promoCode): void
    {
        $mutation = 'mutation {
                        ApplyPromoCodeToCart(
                            input: {
                                cartUuid: "' . $cartUuid . '"
                                promoCode: "' . $promoCode . '"
                            }
                        ) {
                            uuid
                        }
                    }';

        $this->getResponseContentForQuery($mutation);
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
