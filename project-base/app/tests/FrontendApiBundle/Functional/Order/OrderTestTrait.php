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

        $helloKittyName = t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . ' ' .
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . ' ' .
            t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);

        return [
            0 => [
                'name' => $helloKittyName,
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ],
            1 => [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'quantity' => 1,
                'vatRate' => $vatZero->getPercent(),
                'unit' => null,
            ],
            2 => [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => null,
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
        $pickupPlaceIdentifierLine = '';

        if ($pickupPlaceIdentifier !== null) {
            $pickupPlaceIdentifierLine = 'pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"';
        }
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    ' . ($cartUuid !== null ? 'cartUuid: "' . $cartUuid . '"' : '') . '
                    transportUuid: "' . $transport->getUuid() . '"
                    ' . $pickupPlaceIdentifierLine . '
                }) {
                    uuid
                }
            }
        ';
        $this->getResponseContentForQuery($changeTransportInCartMutation);
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
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    ' . ($cartUuid !== null ? 'cartUuid: "' . $cartUuid . '"' : '') . '
                    paymentUuid: "' . $payment->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';
        $this->getResponseContentForQuery($changePaymentInCartMutation);
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
                            differentDeliveryAddress: false
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
