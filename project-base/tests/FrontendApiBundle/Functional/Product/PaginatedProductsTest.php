<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaginatedProductsTest extends GraphQlTestCase
{
    public function testFirstFivePaginatedProductsAreOrderedByOrderingMode(): void
    {
        foreach ($this->getPaginatedProductsDataProvider() as $dataSet) {
            $orderingMode = $dataSet['orderingMode'];
            $expectedOrderedProducts = $dataSet['expectedOrderedProducts'];

            $query = $this->getQueryWithOrderingMode($orderingMode);
            $response = $this->getResponseContentForQuery($query);

            $graphQlType = 'products';
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
            $this->assertArrayHasKey('edges', $responseData);

            $queryResult = [];
            foreach ($responseData['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge);
                $queryResult[] = $edge['node'];
            }

            $this->assertEquals($expectedOrderedProducts, $queryResult, json_encode($queryResult));
        }
    }

    /**
     * @return array<int, array{orderingMode: string, expectedOrderedProducts: array<int, array{name: string}>}>
     */
    private function getPaginatedProductsDataProvider(): array
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        return [
            [
                'orderingMode' => 'NAME_ASC',
                'expectedOrderedProducts' => [
                    ['name' => t('100 Czech crowns ticket', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('27” Hyundai T27D590EY', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('27” Hyundai T27D590EZ', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('30” Hyundai 22MT44D', [], 'dataFixtures', $firstDomainLocale)],
                ],
            ],
            [
                'orderingMode' => 'NAME_DESC',
                'expectedOrderedProducts' => [
                    ['name' => t(
                        'ZN-8009 steam iron Ferrato stainless steel 2200 Watt Blue',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    )],
                    ['name' => t('YENKEE YSP 1005WH white', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Xtreamer SW5', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Xtreamer SW4', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Winch throttle silver VP-9711/12', [], 'dataFixtures', $firstDomainLocale)],
                ],
            ],
            [
                'orderingMode' => 'PRICE_ASC',
                'expectedOrderedProducts' => [
                    ['name' => t(
                        'Reflective tape for safe movement on the road',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    )],
                    ['name' => t('CD-R VERBATIM 210MB', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Prime flour 1 kg', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Million-euro toilet paper', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t(
                        'Aquila Aquagym non-carbonated spring water',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    )],
                ],
            ],
            [
                'orderingMode' => 'PRICE_DESC',
                'expectedOrderedProducts' => [
                    ['name' => t('Samsung UE75HU7500 (UHD)', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('OKI MC861cdxn+ (01318206)', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('OKI MC861cdxm', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('JURA Impressa J9 TFT Carbon', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Canon EOS 700E', [], 'dataFixtures', $firstDomainLocale)],
                ],
            ],
        ];
    }

    /**
     * @param string $orderingMode
     * @return string
     */
    private function getQueryWithOrderingMode(string $orderingMode): string
    {
        return '
            query {
                products (
                    first: 5
                    orderingMode: ' . $orderingMode . '
                ) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';
    }
}
