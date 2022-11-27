<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\VatDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    public function testGetAllCustomerUserOrders(): void
    {
        foreach ($this->getOrdersDataProvider() as $dataSet) {
            [$query, $expectedOrdersData] = $dataSet;

            $graphQlType = 'orders';
            $response = $this->getResponseContentForQuery($query);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('edges', $responseData);
            $this->assertCount(count($expectedOrdersData), $responseData['edges']);

            foreach ($responseData['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge);

                $expectedOrderData = array_shift($expectedOrdersData);
                $this->assertArrayHasKey('status', $edge['node']);
                $this->assertSame($expectedOrderData['status'], $edge['node']['status']);

                $this->assertArrayHasKey('totalPrice', $edge['node']);
                $this->assertArrayHasKey('priceWithVat', $edge['node']['totalPrice']);
                $this->assertSame($expectedOrderData['priceWithVat'], $edge['node']['totalPrice']['priceWithVat']);
            }
        }
    }

    /**
     * @return array<array{0: string, 1: mixed[]}>
     */
    private function getOrdersDataProvider(): array
    {
        return [
            [
                $this->getOrdersWithoutFilterQuery(),
                $this->getExpectedUserOrders(),
            ],
            [
                $this->getFirstOrdersQuery(2),
                array_slice($this->getExpectedUserOrders(), 0, 2),
            ],
            [
                $this->getFirstOrdersQuery(1),
                array_slice($this->getExpectedUserOrders(), 0, 1),
            ],
            [
                $this->getLastOrdersQuery(1),
                array_slice($this->getExpectedUserOrders(), 5, 1),
            ],
            [
                $this->getLastOrdersQuery(2),
                array_slice($this->getExpectedUserOrders(), 4, 2),
            ],
        ];
    }

    /**
     * @return string
     */
    private function getOrdersWithoutFilterQuery(): string
    {
        return '
            {
                orders {
                    edges {
                        node {
                            status
                            totalPrice {
                                priceWithVat
                            }
                        }
                    }
                }
            }
        ';
    }

    /**
     * @param int $numberOfOrders
     * @return string
     */
    private function getFirstOrdersQuery(int $numberOfOrders): string
    {
        return '
            {
                orders (first:' . $numberOfOrders . ') {
                    edges {
                        node {
                            status
                            totalPrice {
                                priceWithVat
                            }
                        }
                    }
                }
            }
        ';
    }

    /**
     * @param int $numberOfOrders
     * @return string
     */
    private function getLastOrdersQuery(int $numberOfOrders): string
    {
        return '
            {
                orders (last:' . $numberOfOrders . ') {
                    edges {
                        node {
                            status
                            totalPrice {
                                priceWithVat
                            }
                        }
                    }
                }
            }
        ';
    }

    /**
     * @return mixed[][]
     */
    private function getExpectedUserOrders(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        $expectedOrderItems1 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.7', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh)],
        ];
        $expectedOrder1 = [
            'status' => t('In Progress', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems1
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems2 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('8173.50', $vatHigh, 8)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('17843.00', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
        ];
        $expectedOrder2 = [
            'status' => t('Done', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems2
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems3 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('263.60', $vatHigh, 6)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('5.00', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh)],
        ];
        $expectedOrder3 = [
            'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems3
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems4 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('1314.10', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('818.00', $vatHigh, 3)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
        ];
        $expectedOrder4 = [
            'status' => t('Done', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems4
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems5 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('437.20', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('180.00', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('429.80', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('3.00', $vatHigh, 5)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
        ];
        $expectedOrder5 = [
            'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems5
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems6 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('98.30', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('19743.60', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('3.00', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('90.10', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('164.50', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('437.20', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh)],
        ];
        $expectedOrder6 = [
            'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
            'priceWithVat' => AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems6
            )->getPriceWithVat()->getAmount(),
        ];

        return [
            $expectedOrder1,
            $expectedOrder2,
            $expectedOrder3,
            $expectedOrder4,
            $expectedOrder5,
            $expectedOrder6,
        ];
    }
}
