<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductVariantTest extends GraphQlTestCase
{
    private Product $productAsMainVariant;

    private Product $productAsVariant;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productAsMainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 83, Product::class);
        $this->productAsVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 75, Product::class);
    }

    public function testProductMainVariantResultData(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $arrayExpected = [
            '__typename' => 'MainVariant',
            'name' => t('Television Sencor [M]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'shortDescription' => t(
                'Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'variants' => [
                [
                    'name' => t('51,5" Sencor [V]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('60" Sencor [V]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'variantsCount' => 2,
            'availableStoresCount' => null,
            'stockQuantity' => null,
            'availability' => [
                'name' => t('In stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                'status' => AvailabilityStatusEnum::IN_STOCK,
            ],
            'storeAvailabilities' => [],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/mainVariantByUuid.graphql', [
            'uuid' => $this->productAsMainVariant->getUuid(),
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'product');
        $this->assertSame($arrayExpected, $responseData);
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
                    'name' => t('27" Hyundai [V]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'shortDescription' => t(
                        'TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A +',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'mainVariant' => [
                        'name' => t('Television Hyundai [M]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
