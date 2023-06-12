<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AbstractOrderTestCase extends GraphQlTestCase
{
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
                'name' => t('Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
            '___UUID_PAYMENT___' => $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY)->getUuid(),
            '___UUID_TRANSPORT___' => $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST)->getUuid(),
            '___UUID_PRODUCT___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1')->getUuid(),
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
     * @param string $cartUuid
     */
    protected function addCzechPostTransportToCart(string $cartUuid): void
    {
        /** @var \App\Model\Transport\Transport $transportCzechPost */
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $this->addTransportToCart($cartUuid, $transportCzechPost);
    }

    protected function addPplTransportToDemoCart(): void
    {
        /** @var \App\Model\Transport\Transport $transportPpl */
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $this->addTransportToCart(CartDataFixture::CART_UUID, $transportPpl);
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    protected function addTransportToCart(
        string $cartUuid,
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
                    cartUuid: "' . $cartUuid . '"
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
     * @param string $cartUuid
     */
    protected function addCashOnDeliveryPaymentToCart(string $cartUuid): void
    {
        /** @var \App\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $this->addPaymentToCart($cartUuid, $paymentCashOnDelivery);
    }

    /**
     * @param string $cartUuid
     */
    protected function addCardPaymentToCart(string $cartUuid): void
    {
        /** @var \App\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $this->addPaymentToCart($cartUuid, $paymentCard);
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Payment\Payment $payment
     */
    protected function addPaymentToCart(string $cartUuid, Payment $payment): void
    {
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . $cartUuid . '"
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
        /** @var \App\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
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
