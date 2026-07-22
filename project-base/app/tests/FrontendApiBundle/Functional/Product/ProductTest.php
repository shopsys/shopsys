<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ParameterColorValueDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductTest extends GraphQlTestCase
{
    /**
     * @var array<string>
     * @see \Tests\FrontendApiBundle\Functional\Hreflang\HreflangLinksTest::testAlternateDomainLanguages for 'hreflangLinks' field coverage test
     * @see \Tests\FrontendApiBundle\Functional\Image\ProductImagesTest::testFirstProductWithAllImages for 'images' and 'mainImage' fields coverage test
     */
    private const array FIELDS_EXCLUDED_FROM_COVERAGE_TEST = [
        'hreflangLinks',
        'images',
        'mainImage',
        // zbozi category is not filled on English domain
        'zboziCategory',
    ];

    private Product $product;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testProductDetailNameByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/productByUuid.graphql', [
            'uuid' => $this->product->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $expectedName = t(
            '22" Sencor SLE 22F46DM4 HELLO KITTY',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->getLocaleForFirstDomain(),
        );

        $this->assertSame($expectedName, $data['name']);
    }

    public function testProductDetailWithAllAttributesByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDetailWithAllAttributes.graphql', [
            'uuid' => $this->product->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $expectedData = $this->getExpectedProductDetailWithAllAttributes();

        foreach ($expectedData as $field => $expectedValue) {
            $this->assertArrayHasKey($field, $data, sprintf('Field "%s" is missing in response', $field));
            $this->assertSame($expectedValue, $data[$field], sprintf('Field "%s" does not match expected value', $field));
        }

        $unexpectedFields = array_diff(array_keys($data), array_keys($expectedData));
        $this->assertEmpty($unexpectedFields, sprintf('Unexpected fields in response: %s', implode(', ', $unexpectedFields)));
    }

    private function getExpectedProductDetailWithAllAttributes(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $shortDescription = t(
            'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $firstDomainLocale,
        );

        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId(), Vat::class);

        $fullName = sprintf(
            '%s %s %s',
            t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $mainCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $subCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);

        return [
            'id' => 1,
            'uuid' => '55bb22ab-bb88-5459-a464-005b948d8c78',
            'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'slug' => '/' . $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1], UrlGeneratorInterface::RELATIVE_PATH),
            'shortDescription' => $shortDescription,
            'seoH1' => t(
                'Hello Kitty Television',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'seoTitle' => t(
                'Hello Kitty TV',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'seoMetaDescription' => t(
                'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
            'unit' => [
                'name' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ],
            'availability' => [
                'name' => t('In stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                'status' => AvailabilityStatusEnum::IN_STOCK,
            ],
            'stockQuantity' => 2700,
            'imagesCount' => 2,
            'isAllowedNegativeStock' => true,
            'expectedRestockingDate' => null,
            'categories' => [
                [
                    'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'flags' => [
                [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'rgbColor' => '#e8111c',
                ],
            ],
            'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
            'giftPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('24', $vatHigh),
            'brand' => [
                'name' => 'Sencor',
            ],
            'accessories' => [
                [
                    'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Television Philips [M]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('CD-R VERBATIM 210MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t(
                        'Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                ],
                [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('24" Philips [V]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon PIXMA MG2450', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'additionalServices' => [
                [
                    'name' => t('Professional assembly', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'catnum' => 'SERVICE-ASSEMBLY',
                ],
                [
                    'name' => t('Extended warranty for 5 years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'catnum' => 'SERVICE-WARRANTY',
                ],
                [
                    'name' => t('Gift wrapping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'catnum' => 'SERVICE-GIFT-WRAP',
                ],
                [
                    'name' => t('Custom engraving', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'catnum' => 'SERVICE-ENGRAVING',
                ],
                [
                    'name' => t('Old appliance removal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'catnum' => 'SERVICE-APPLIANCE-REMOVAL',
                ],
            ],
            'isSellingDenied' => false,
            'isPersonalPickupOnly' => false,
            'isCurrentlyOutOfStock' => false,
            'description' => t(
                'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'orderingPriority' => 9,
            'parameters' => [
                [
                    'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => [
                        'name' => t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'values' => [
                        [
                            'text' => '27',
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Connection method', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Connection method', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => '#ff0000',
                            'colorIcon' => $this->getRedColorExpectedFile($firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
                [
                    'name' => t('Warranty', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => [
                        'name' => t('years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'values' => [
                        [
                            'text' => '3',
                            'rgbHex' => null,
                            'colorIcon' => null,
                        ],
                    ],
                ],
            ],
            'namePrefix' => t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'nameSuffix' => t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'fullName' => $fullName,
            'catalogNumber' => '9177759',
            'partNumber' => 'SLE 22F46DM4',
            'ean' => '8845781245930',
            'usps' => [
                t(
                    'Hello Kitty approved',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Immersive Full HD resolution',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Energy-Efficient Design',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Wide Color Gamut',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Adaptive Sync Technology',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            'storeAvailabilities' => [
                [
                    'store' => [
                        'name' => 'Ostrava',
                    ],
                    'availabilityInformation' => t('Available immediately', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Pardubice',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Brno',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Praha',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Hradec Králové',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Olomouc',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Liberec',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
                [
                    'store' => [
                        'name' => 'Plzeň',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::CUSTOMER_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
            ],
            'availableStoresCount' => 1,
            'breadcrumb' => [
                [
                    'name' => $mainCategory->getName($firstDomainLocale),
                    'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $mainCategory->getId()]),
                ],
                [
                    'name' => $subCategory->getName($firstDomainLocale),
                    'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $subCategory->getId()]),
                ],
                [
                    'name' => $fullName,
                    'slug' => $this->urlGenerator->generate('front_product_detail', ['id' => $this->product->getId()]),
                ],
            ],
            'vatPercent' => '21.000000',
            'files' => $this->getFilesByEntity($this->product),
            'isVisible' => true,
            'isInquiryType' => false,
            'productType' => strtoupper(ProductTypeEnum::TYPE_BASIC),
            'productVideos' => [
                [
                    'token' => 'Wz9zttavXpo',
                    'description' => t('Get to know Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'gifts' => [],
            'promotionBuyQuantity' => null,
            'promotionFreeQuantity' => null,
            'relatedProducts' => $this->getExpectedRelatedProducts($firstDomainLocale),
            'isMainVariant' => false,
            'reviewsSummary' => [
                'averageRating' => 3.44,
                'ratingCounts' => [
                    ['rating' => 5, 'count' => 2],
                    ['rating' => 4, 'count' => 3],
                    ['rating' => 3, 'count' => 2],
                    ['rating' => 2, 'count' => 1],
                    ['rating' => 1, 'count' => 1],
                ],
                'totalCount' => 9,
            ],

        ];
    }

    public function testAllProductFieldsAreCovered(): void
    {
        $schemaFields = $this->getFieldNamesForType('Product', self::FIELDS_EXCLUDED_FROM_COVERAGE_TEST);
        $testedFields = array_keys($this->getExpectedProductDetailWithAllAttributes());

        $missingFields = array_diff($schemaFields, $testedFields);

        if (count($missingFields) > 0) {
            $this->fail(
                sprintf(
                    "The following Product fields are not covered in the tests:\n- %s\n\nPlease add them to ProductDetailWithAllAttributes.graphql and getExpectedProductDetailWithAllAttributes(), or exclude them from this test using FIELDS_EXCLUDED_FROM_COVERAGE_TEST constant.",
                    implode("\n- ", $missingFields),
                ),
            );
        }
    }

    /**
     * @return array[]
     */
    private function getExpectedRelatedProducts(string $firstDomainLocale): array
    {
        return [
            [
                'name' => t(
                    '32" Philips 32PFL4308',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            [
                'name' => t(
                    '47" LG 47LA790V (FHD)',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            [
                'name' => t(
                    'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            [
                'name' => t(
                    'Apple iPhone 5S 64GB, gold',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
        ];
    }

    private function getRedColorExpectedFile(string $locale): array
    {
        $redColorParameterValue = $this->getReference(ParameterColorValueDataFixture::PARAMETER_VALUE_RED_REFERENCE_PREFIX . $locale, ParameterValue::class);
        $allFilesArray = $this->getFilesByEntity($redColorParameterValue);

        return array_first($allFilesArray);
    }
}
