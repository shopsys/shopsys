<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    public function testGetAllCustomerUserOrders(): void
    {
        foreach ($this->getOrdersDataProvider() as $dataSet) {
            list($query, $expectedOrdersData) = $dataSet;

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
     * @return array
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
     * @return array
     */
    private function getExpectedUserOrders(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            [
                'status' => t('In Progress', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '153.640000',
            ],
            [
                'status' => t('Done', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '4308.320000',
            ],
            [
                'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '83.580000',
            ],
            [
                'status' => t('Done', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '245.970000',
            ],
            [
                'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '76.580000',
            ],
            [
                'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
                'priceWithVat' => '1012.420000',
            ],
        ];
    }
}
