<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsTest extends GraphQlTestCase
{
    public function testFirstFiveProductsWithName(): void
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
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

        $edges = $this->getResponseContentForQuery($query)['data']['products']['edges'];
        $queryResult = [];
        foreach ($edges as $edge) {
            $queryResult[] = $edge['node'];
        }

        $this->assertEquals($productsExpected, $queryResult, json_encode($queryResult));
    }

    public function testNineteenthProductWithAllAttributes(): void
    {
        $query = '
            query {
                products (first: 1, after: "YXJyYXljb25uZWN0aW9uOjE3") {
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

        $arrayExpected = $this->getExpectedDataForNineteenthProduct();

        $edges = $this->getResponseContentForQuery($query)['data']['products']['edges'];
        $queryResult = [];
        foreach ($edges as $edge) {
            $queryResult[] = $edge['node'];
        }

        $this->assertEquals($arrayExpected, $queryResult, json_encode($queryResult));
    }

    /**
     * @return array
     */
    private function getExpectedDataForNineteenthProduct(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $arrayExpected = [
            [
                'name' => t('Canon EOS 700D', [], 'dataFixtures', $firstDomainLocale),
                'shortDescription' => t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light', [], 'dataFixtures', $firstDomainLocale),
                'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 8]),
                'unit' => [
                    'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                ],
                'availability' => [
                    'name' => t('In stock', [], 'dataFixtures', $firstDomainLocale),
                ],
                'stockQuantity' => 100,
                'categories' => [
                    [
                        'name' => t('Cameras & Photo', [], 'dataFixtures', $firstDomainLocale),
                    ],
                ],
                'flags' => [],
                'price' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('24990'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('24990'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('0.00'),
                ],
            ],
        ];

        return $arrayExpected;
    }
}
