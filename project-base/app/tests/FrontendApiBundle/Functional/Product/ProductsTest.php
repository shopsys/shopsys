<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ProductsTest extends ProductsGraphQlTestCase
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
            ['name' => t('100 Czech crowns ticket', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('27” Hyundai T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('27” Hyundai T27D590EZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('30” Hyundai 22MT44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
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
                            },
                            brand {
                                name
                            }
                            accessories {
                                name
                            }
                            isSellingDenied
                            description
                            orderingPriority
                            parameters {
                                name
                                values {
                                    text
                                }
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
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        return [
            [
                'name' => t('30” Hyundai 22MT44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'shortDescription' => t(
                    'Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 77]),
                'unit' => [
                    'name' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                'availability' => [
                    'name' => t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                'stockQuantity' => 100,
                'categories' => [
                    [
                        'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
                'flags' => [],
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('3999', $vatHigh),
                'brand' => [
                    'name' => 'Hyundai',
                ],
                'accessories' => [],
                'isSellingDenied' => false,
                'description' => t(
                    'Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768, DVB-T/C, HDMI, SCART, D-Sub, USB, speakers, Energ. Class A',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                'orderingPriority' => 1,
                'parameters' => [
                    [
                        'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'values' => [
                            [
                                'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            ],
                        ],
                    ],
                    [
                        'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'values' => [
                            [
                                'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            ],
                        ],
                    ],
                    [
                        'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'values' => [
                            [
                                'text' => t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            ],
                        ],
                    ],
                    [
                        'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'values' => [
                            [
                                'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            ],
                        ],
                    ],
                    [
                        'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'values' => [
                            [
                                'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            ],
                        ],
                    ],
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
            'Reflective tape for safe movement on the road',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $firstDomainLocale,
        ) . '"
                    }
                }
            ]
        }
    }
}';
    }
}
