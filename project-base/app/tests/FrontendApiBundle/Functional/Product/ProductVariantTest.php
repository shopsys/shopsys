<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductVariantTest extends GraphQlTestCase
{
    private Product $productAsMainVariant;

    private Product $productAsVariant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productAsMainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 83);
        $this->productAsVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 75);
    }

    public function testProductMainVariantResultData(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->productAsMainVariant->getUuid() . '") {
                    __typename,
                    name,
                    shortDescription
                    ...on MainVariant {
                      variants {
                        name
                      }
                    }
                }
            }
        ';

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $arrayExpected = [
            'data' => [
                'product' => [
                    '__typename' => 'MainVariant',
                    'name' => t('Hyundai 22HD44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'shortDescription' => t(
                        'Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'variants' => [
                        // Variant 51,5" Hyundai 22HD44D is not sellable, so it's not present
                        [
                            'name' => t('60" Hyundai 22HD44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testProductVariantResultData(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->productAsVariant->getUuid() . '") {
                    __typename,
                    name,
                    shortDescription
                    ...on Variant {
                      mainVariant {
                        name
                      }
                    }
                }
            }
        ';

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $arrayExpected = [
            'data' => [
                'product' => [
                    '__typename' => 'Variant',
                    'name' => t('27" Hyundai T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'shortDescription' => t(
                        'TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A +',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'mainVariant' => [
                        'name' => t('32" Hyundai 32PFL4400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
