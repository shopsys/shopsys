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
                products {
                    name
                }
            }
        ';

        $productsExpected = [
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('47" LG 47LA790V (FHD)', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Apple iPhone 5S 64GB, gold', [], 'dataFixtures', $firstDomainLocale)],
        ];

        $queryResult = array_slice($this->getResponseContentForQuery($query)['data']['products'], 0, 5);

        $this->assertEquals($productsExpected, $queryResult, json_encode($queryResult));
    }

    public function testSeventhProductWithAllAttributes(): void
    {
        $query = '
            query {
                products {
                    name,
                    shortDescription,
                    link,
                    unit {
                        name
                    },
                    availability {
                        name
                    },
                    stockQuantity,
                    categories {
                      name
                    },
                    flags {
                      name, rgbColor
                    },
                    price {
                      priceWithVat,
                      priceWithoutVat,
                      vatAmount
                    }
                }
            }
        ';

        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $arrayExpected = [
            [
                'name' => t('Canon EOS 700D', [], 'dataFixtures', $firstDomainLocale),
                'shortDescription' => t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light', [], 'dataFixtures', $firstDomainLocale),
                'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 8]),
                'unit' => [
                    'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                ],
                'availability' => null,
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

        $queryResult = array_slice($this->getResponseContentForQuery($query)['data']['products'], 6, 1);

        $this->assertEquals($arrayExpected, $queryResult, json_encode($queryResult));
    }
}
