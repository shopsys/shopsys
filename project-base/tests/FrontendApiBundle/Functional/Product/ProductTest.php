<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $product;

    protected function setUp(): void
    {
        $productFacade = self::getContainer()->get(ProductFacade::class);

        /** @var \App\Model\Product\Product $product */
        $product = $productFacade->getById(1);
        $this->product = $product;

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
                    'name' => t(
                        '22" Sencor SLE 22F46DM4 HELLO KITTY',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $this->getLocaleForFirstDomain()
                    ),
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
                    seoH1,
                    seoTitle,
                    seoMetaDescription
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
                        name, 
                        rgbColor
                    },
                    price {
                        priceWithVat,
                        priceWithoutVat,
                        vatAmount
                    },
                    brand {
                        name
                    },
                    accessories {
                        name
                    },
                    isSellingDenied,
                    description,
                    orderingPriority
                    parameters {
                        name
                        values {
                            text
                        }
                    }
                    images(size: "original") {
                        name
                        position
                        type
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
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $shortDescription = t(
            'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $firstDomainLocale
        );

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        return [
            'data' => [
                'product' => [
                    'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'shortDescription' => $shortDescription,
                    'seoH1' => t(
                        'Hello Kitty Television',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
                    ),
                    'seoTitle' => t(
                        'Hello Kitty TV',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
                    ),
                    'seoMetaDescription' => t(
                        'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
                    ),
                    'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
                    'unit' => [
                        'name' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'availability' => [
                        'name' => t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'stockQuantity' => 300,
                    'categories' => [
                        [
                            'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                        [
                            'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                    'flags' => [
                        [
                            'name' => t('TOP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbColor' => '#d6fffa',
                        ],
                        [
                            'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbColor' => '#f9ffd6',
                        ],
                    ],
                    'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                    'brand' => [
                        'name' => 'Sencor',
                    ],
                    'accessories' => [
                        [
                            'name' => t(
                                'Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD',
                                [],
                                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                                $firstDomainLocale
                            ),
                        ],
                        [
                            'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                    'isSellingDenied' => false,
                    'description' => t(
                        'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
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
                                    'text' => t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
                    'images' => [
                        0 => [
                            'name' => 'Product 1 image',
                            'position' => null,
                            'type' => null,
                        ],
                        1 => [
                            'name' => 'Product 1 image',
                            'position' => null,
                            'type' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}
