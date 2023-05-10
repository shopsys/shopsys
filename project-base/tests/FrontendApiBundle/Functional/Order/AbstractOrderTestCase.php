<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
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
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh, 10),
                'quantity' => 10,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ],
            1 => [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            2 => [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
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
                Money::create($expectedOrderItemTotalPrice['priceWithVat'])
            );
            $totalPriceWithoutVat = $totalPriceWithoutVat->add(
                Money::create($expectedOrderItemTotalPrice['priceWithoutVat'])
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
}
