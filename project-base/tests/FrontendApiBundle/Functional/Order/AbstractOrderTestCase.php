<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AbstractOrderTestCase extends GraphQlTestCase
{
    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            0 => [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => '139.96',
                    'priceWithoutVat' => '115.67',
                    'vatAmount' => '24.29',
                ],
                'totalPrice' => [
                    'priceWithVat' => '1399.60',
                    'priceWithoutVat' => '1156.69',
                    'vatAmount' => '242.91',
                ],
                'quantity' => 10,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            1 => [
                'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => '2.00',
                    'priceWithoutVat' => '2.00',
                    'vatAmount' => '0.00',
                ],
                'totalPrice' => [
                    'priceWithVat' => '2.00',
                    'priceWithoutVat' => '2.00',
                    'vatAmount' => '0.00',
                ],
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            2 => [
                'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => '4.84',
                    'priceWithoutVat' => '4.00',
                    'vatAmount' => '0.84',
                ],
                'totalPrice' => [
                    'priceWithVat' => '4.84',
                    'priceWithoutVat' => '4.00',
                    'vatAmount' => '0.84',
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
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
}
