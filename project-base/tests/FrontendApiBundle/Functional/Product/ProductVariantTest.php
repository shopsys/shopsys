<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductVariantTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $productAsMainVariant;

    /**
     * @var \App\Model\Product\Product
     */
    private Product $productAsVariant;

    protected function setUp(): void
    {
        $productFacade = self::getContainer()->get(ProductFacade::class);

        /** @var \App\Model\Product\Product $productAsMainVariant */
        $productAsMainVariant = $productFacade->getById(150);
        $this->productAsMainVariant = $productAsMainVariant;

        /** @var \App\Model\Product\Product $productAsVariant */
        $productAsVariant = $productFacade->getById(75);
        $this->productAsVariant = $productAsVariant;

        parent::setUp();
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
                    'name' => t('Hyundai 22HD44D', [], 'dataFixtures', $firstDomainLocale),
                    'shortDescription' => t(
                        'Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'variants' => [
                        // Variant 51,5” Hyundai 22HD44D is not sellable, so it's not present
                        [
                            'name' => t('60” Hyundai 22HD44D', [], 'dataFixtures', $firstDomainLocale),
                        ],
                        [
                            'name' => t('Hyundai 22HD44D', [], 'dataFixtures', $firstDomainLocale),
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
                    'name' => t('27” Hyundai T27D590EY', [], 'dataFixtures', $firstDomainLocale),
                    'shortDescription' => t(
                        'TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A +',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'mainVariant' => [
                        'name' => t('32” Hyundai 32PFL4400', [], 'dataFixtures', $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
