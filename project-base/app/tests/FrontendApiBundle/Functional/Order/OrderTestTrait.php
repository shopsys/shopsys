<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

trait OrderTestTrait
{
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
                'name' => $helloKittyProduct->getFullname($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => OrderItemTypeEnum::TYPE_PRODUCT,
                'product' => [
                    'uuid' => $helloKittyProduct->getUuid(),
                ],
            ],
            1 => [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'quantity' => 1,
                'vatRate' => $vatZero->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_PAYMENT,
                'product' => null,
            ],
            2 => [
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
     * @param string $filePath
     * @return string
     */
    protected function getOrderMutation(string $filePath): string
    {
        $mutation = file_get_contents($filePath);

        $replaces = [
            '___UUID_PAYMENT___' => $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class)->getUuid(),
            '___UUID_TRANSPORT___' => $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class)->getUuid(),
            '___UUID_PRODUCT___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class)->getUuid(),
        ];

        return strtr($mutation, $replaces);
    }

    /**
     * @param array $expectedOrderItems
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public static function getOrderTotalPriceByExpectedOrderItems(array $expectedOrderItems): Price
    {
        $totalPriceWithVat = Money::zero();
        $totalPriceWithoutVat = Money::zero();

        foreach ($expectedOrderItems as $expectedOrderItem) {
            $expectedOrderItemTotalPrice = $expectedOrderItem['totalPrice'];
            $totalPriceWithVat = $totalPriceWithVat->add(
                Money::create($expectedOrderItemTotalPrice['priceWithVat']),
            );
            $totalPriceWithoutVat = $totalPriceWithoutVat->add(
                Money::create($expectedOrderItemTotalPrice['priceWithoutVat']),
            );
        }

        return new Price($totalPriceWithoutVat, $totalPriceWithVat);
    }

    /**
     * @param string|null $cartUuid
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    protected function addTransportToCart(
        ?string $cartUuid,
        Transport $transport,
        ?string $pickupPlaceIdentifier = null,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'transportUuid' => $transport->getUuid(),
            'pickupPlaceIdentifier' => $pickupPlaceIdentifier,

        ]);

        $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');
    }

    /**
     * @param array $expectedOrderItems
     * @return array
     */
    public static function getSerializedOrderTotalPriceByExpectedOrderItems(array $expectedOrderItems): array
    {
        $price = static::getOrderTotalPriceByExpectedOrderItems($expectedOrderItems);

        return [
            'priceWithVat' => $price->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $price->getPriceWithoutVat()->getAmount(),
            'vatAmount' => $price->getVatAmount()->getAmount(),
        ];
    }

    /**
     * @param string|null $cartUuid
     */
    protected function addCzechPostTransportToCart(?string $cartUuid): void
    {
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);
        $this->addTransportToCart($cartUuid, $transportCzechPost);
    }

    /**
     * @param string|null $cartUuid
     */
    protected function addPplTransportToCart(?string $cartUuid): void
    {
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $this->addTransportToCart($cartUuid, $transportPpl);
    }

    /**
     * @param string|null $cartUuid
     */
    protected function addCashOnDeliveryPaymentToCart(?string $cartUuid): void
    {
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class);
        $this->addPaymentToCart($cartUuid, $paymentCashOnDelivery);
    }

    /**
     * @param string|null $cartUuid
     */
    protected function addCardPaymentToCart(?string $cartUuid): void
    {
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->addPaymentToCart($cartUuid, $paymentCard);
    }

    /**
     * @param string|null $cartUuid
     * @param \App\Model\Payment\Payment $payment
     */
    protected function addPaymentToCart(?string $cartUuid, Payment $payment): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'paymentUuid' => $payment->getUuid(),

        ]);

        $this->getResponseDataForGraphQlType($response, 'ChangePaymentInCart');
    }

    protected function addCardPaymentToDemoCart(): void
    {
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->addPaymentToCart(CartDataFixture::CART_UUID, $paymentCard);
    }

    /**
     * @return string
     */
    protected function getCreateOrderMutationFromDemoCart(): string
    {
        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . CartDataFixture::CART_UUID . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            isDeliveryAddressDifferentFromBilling: false
                        }
                    ) {
                        order {
                            uuid
                        }
                        cart {
                            modifications {
                                paymentModifications {
                                    paymentPriceChanged
                                    paymentUnavailable
                                }
                                transportModifications {
                                    transportUnavailable
                                    transportPriceChanged
                                    personalPickupStoreUnavailable
                                    transportWeightLimitExceeded
                                }
                            }
                        }
                    }
                }';
    }
}
