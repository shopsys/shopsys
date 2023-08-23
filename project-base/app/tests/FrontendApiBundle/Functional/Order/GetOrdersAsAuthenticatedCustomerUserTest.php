<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    public function testGetAllCustomerUserOrders(): void
    {
        $this->markTestSkipped('This test is skipped because of the issue with rounding');

        // @phpstan-ignore-next-line Test is skipped
        foreach ($this->getOrdersDataProvider() as $datasetIndex => $dataSet) {
            [$query, $expectedOrdersData] = $dataSet;

            $graphQlType = 'orders';
            $response = $this->getResponseContentForQuery($query);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $message = sprintf('Dataset index: %d', $datasetIndex);

            $this->assertArrayHasKey('edges', $responseData, $message);
            $this->assertCount(count($expectedOrdersData), $responseData['edges'], $message);

            foreach ($responseData['edges'] as $orderIndex => $edge) {
                $orderMessage = $message . sprintf(' [Order index: %d]', $orderIndex);
                $this->assertArrayHasKey('node', $edge, $orderMessage);

                $expectedOrderData = array_shift($expectedOrdersData);
                $this->assertArrayHasKey('status', $edge['node'], $orderMessage);
                $this->assertSame($expectedOrderData['status'], $edge['node']['status'], $orderMessage);

                $this->assertArrayHasKey('totalPrice', $edge['node'], $orderMessage);
                $this->assertArrayHasKey('priceWithVat', $edge['node']['totalPrice'], $orderMessage);
                $this->assertSame($expectedOrderData['priceWithVat'], $edge['node']['totalPrice']['priceWithVat'], $orderMessage);
            }
        }
    }

    /**
     * @return iterable
     * @phpstan-ignore-next-line Test is skipped
     */
    private function getOrdersDataProvider(): iterable
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
     * @return array
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
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh)],
        ];
        $expectedOrder1 = [
            'status' => t('In Progress', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems1,
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems2 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('8173.55', $vatHigh, 8)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('17842.98', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.74', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero)],
        ];
        $expectedOrder2 = [
            'status' => t('Done', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems2,
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems3 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('263.64', $vatHigh, 6)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('4.96', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero)],
        ];
        $expectedOrder3 = [
            'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems3,
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems4 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('1314.05', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('818.18', $vatHigh, 3)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh)],
        ];
        $expectedOrder4 = [
            'status' => t('Done', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems4,
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems5 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('437.19', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('180.17', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('429.75', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('3.31', $vatHigh, 5)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatHigh)],
        ];
        $expectedOrder5 = [
            'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems5,
            )->getPriceWithVat()->getAmount(),
        ];

        $expectedOrderItems6 = [
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('98.35', $vatHigh, 2)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('19743.80', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('3.31', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('90.08', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('164.46', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('437.19', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero)],
        ];
        $expectedOrder6 = [
            'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'priceWithVat' => self::getOrderTotalPriceByExpectedOrderItems(
                $expectedOrderItems6,
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
