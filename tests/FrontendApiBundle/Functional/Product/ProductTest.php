<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private $product;

    protected function setUp(): void
    {
        $productFacade = $this->getContainer()->get(ProductFacade::class);
        $this->product = $productFacade->getById(1);

        parent::setUp();
    }

    public function testProductDetailNameByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'product' => [
                    'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testProductDetailWithAllAttributesByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name,
                    shortDescription,
                    link,
                    unit {
                        name
                    },
                    availability{
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

        $this->assertQueryWithExpectedArray($query, $this->getExpectedProductDetailWithAllAttributes());
    }

    /**
     * @return array
     */
    private function getExpectedProductDetailWithAllAttributes(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $shortDescription = t(
            'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback',
            [],
            'dataFixtures',
            $firstDomainLocale
        );

        return [
            'data' => [
                'product' => [
                    'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
                    'shortDescription' => $shortDescription,
                    'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
                    'unit' => [
                        'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'availability' => [
                        'name' => t('In stock', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'stockQuantity' => 300,
                    'categories' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                        ],
                        [
                            'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                        ],
                    ],
                    'flags' => [
                        [
                            'name' => t('TOP', [], 'dataFixtures', $firstDomainLocale),
                            'rgbColor' => '#d6fffa',
                        ],
                        [
                            'name' => t('Action', [], 'dataFixtures', $firstDomainLocale),
                            'rgbColor' => '#f9ffd6',
                        ],
                    ],
                    'price' => [
                        'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('3499'),
                        'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('2891.74'),
                        'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('607.26'),
                    ],
                ],
            ],
        ];
    }
}
