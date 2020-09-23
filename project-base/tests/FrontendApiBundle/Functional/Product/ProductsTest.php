<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsTest extends GraphQlTestCase
{
    public function testFirstFiveProductsWithName(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $query = '
            query {
                products (first: 5) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t('100 Czech crowns ticket', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('27” Hyundai T27D590EY', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('27” Hyundai T27D590EZ', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('30” Hyundai 22MT44D', [], 'dataFixtures', $firstDomainLocale)],
        ];

        $graphQlType = 'products';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $this->assertArrayHasKey('edges', $responseData);

        $queryResult = [];
        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $queryResult[] = $edge['node'];
        }

        $this->assertEquals($productsExpected, $queryResult, json_encode($queryResult));
    }

    public function testFifthProductWithAllAttributes(): void
    {
        $query = '
            query {
                products (first: 1, after: "YXJyYXljb25uZWN0aW9uOjM=") {
                    edges {
                        node {
                            name
                            shortDescription
                            link
                            unit {
                                name
                            }
                            availability {
                                name
                            }
                            stockQuantity
                            categories {
                                name
                            }
                            flags {
                                name
                                rgbColor
                            }
                            price {
                                priceWithVat
                                priceWithoutVat
                                vatAmount
                            }
                        }
                    }
                }
            }
        ';

        $arrayExpected = $this->getExpectedDataForFifthProduct();

        $graphQlType = 'products';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $this->assertArrayHasKey('edges', $responseData);

        $queryResult = [];
        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $queryResult[] = $edge['node'];
        }

        $this->assertEquals($arrayExpected, $queryResult, json_encode($queryResult));
    }

    /**
     * @return array
     */
    private function getExpectedDataForFifthProduct(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            [
                'name' => t('30” Hyundai 22MT44D', [], 'dataFixtures', $firstDomainLocale),
                'shortDescription' => t(
                    'Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768',
                    [],
                    'dataFixtures',
                    $firstDomainLocale
                ),
                'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 77]),
                'unit' => [
                    'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                ],
                'availability' => [
                    'name' => t('In stock', [], 'dataFixtures', $firstDomainLocale),
                ],
                'stockQuantity' => 100,
                'categories' => [
                    [
                        'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                    ],
                ],
                'flags' => [],
                'price' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('4838.75'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('3999'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('839.75'),
                ],
            ],
        ];
    }

    public function testLastProduct(): void
    {
        $query = '
            query {
                products (last: 1) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $jsonExpected = $this->getExpectedDataForLastProduct();

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    /**
     * @return string
     */
    private function getExpectedDataForLastProduct(): string
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return '{
    "data": {
        "products": {
            "edges": [
                {
                    "node": {
                        "name": "' . t(
            'ZN-8009 steam iron Ferrato stainless steel 2200 Watt Blue',
            [],
            'dataFixtures',
            $firstDomainLocale
        ) . '"
                    }
                }
            ]
        }
    }
}';
    }
}
