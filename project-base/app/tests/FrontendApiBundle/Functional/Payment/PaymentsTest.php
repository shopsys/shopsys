<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentsTest extends GraphQlTestCase
{
    public function testPayments(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'payments');
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId());

        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $arrayExpected = [
            [
                'name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'Quick, cheap and reliable!',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instruction' => null,
                'position' => 0,
                'type' => 'basic',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/payment/53.jpg'),
                        'name' => PaymentDataFixture::PAYMENT_CARD,
                    ],
                ],
                'transports' => [
                    ['name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => null,
            ],
            [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => null,
                'instruction' => null,
                'position' => 1,
                'type' => 'basic',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/payment/55.jpg'),
                        'name' => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
                    ],
                ],
                'transports' => [
                    ['name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => null,
            ],
            [
                'name' => t('Cash', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => null,
                'instruction' => null,
                'position' => 2,
                'type' => 'basic',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/payment/54.jpg'),
                        'name' => PaymentDataFixture::PAYMENT_CASH,
                    ],
                ],
                'transports' => [
                    ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => null,
            ],
            [
                'name' => t('GoPay - Payment By Card [%locale%]', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => null,
                'instruction' => null,
                'position' => 3,
                'type' => 'goPay',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh),
                'images' => [],
                'transports' => [
                    ['name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => [
                    'identifier' => 'PAYMENT_CARD',
                    'name' => sprintf('[%s] Credit card', $firstDomainLocale),
                    'imageNormalUrl' => 'https://gate.gopay.cz/images/checkout/payment_card.png',
                    'imageLargeUrl' => 'https://gate.gopay.cz/images/checkout/payment_card@2x.png',
                    'paymentGroup' => 'card-payment',
                ],
            ],
            [
                'name' => t('GoPay - Quick Bank Account Transfer [%locale%]', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t('Quick and Safe payment via bank account transfer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'instruction' => null,
                'position' => 4,
                'type' => 'goPay',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh),
                'images' => [],
                'transports' => [
                    ['name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => [
                    'identifier' => 'BANK_ACCOUNT',
                    'name' => sprintf('[%s] Quick bank account transfer', $firstDomainLocale),
                    'imageNormalUrl' => 'https://gate.gopay.cz/images/checkout/bank_account.png',
                    'imageLargeUrl' => 'https://gate.gopay.cz/images/checkout/bank_account@2x.png',
                    'paymentGroup' => 'bank-transfer',
                ],
            ],
            [
                'name' => t('Pay later', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => null,
                'instruction' => null,
                'position' => 7,
                'type' => 'basic',
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatZero),
                'images' => [],
                'transports' => [
                    ['name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'goPayPaymentMethod' => null,
            ],
        ];

        $this->assertSame($arrayExpected, $responseData);
    }
}
