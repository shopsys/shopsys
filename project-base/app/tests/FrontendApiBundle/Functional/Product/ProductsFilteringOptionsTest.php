<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterColorValueDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorterHelper;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsFilteringOptionsTest extends GraphQlTestCase
{
    private const PARAMETER_HDMI = 5;

    private string $firstDomainLocale;

    /**
     * @inject
     */
    private ArraySorterHelper $arraySorterHelper;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->firstDomainLocale = $this->getLocaleForFirstDomain();
    }

    public function testGetElectronicsFilterOptions(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('319');
        } else {
            $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('318.85');
        }

        $maximalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('31448');

        $materials = [
            [
                'text' => t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 2,
                'isAbsolute' => true,
            ],
            [
                'text' => t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $materials, $this->getLocaleForFirstDomain());

        $colors = [
            [
                'text' => t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 2,
                'isAbsolute' => true,
                'rgbHex' => '#000000',
                'colorIcon' => null,
            ],
            [
                'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 3,
                'isAbsolute' => true,
                'rgbHex' => '#ff0000',
                'colorIcon' => $this->getRedColorExpectedFile(),
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $colors, $this->getLocaleForFirstDomain());

        $screenSizes = [
            [
                'text' => '27',
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => '30',
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => '47',
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $screenSizes, $this->getLocaleForFirstDomain());

        $hdmiValues = [
            [
                'text' => t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 2,
                'isAbsolute' => true,
            ],
            [
                'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 2,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $hdmiValues, $this->getLocaleForFirstDomain());

        $expectedFlagFilterOptions = [
            [
                'flag' => [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 3,
                'isAbsolute' => true,
            ],
            [
                'flag' => [
                    'name' => t('Promotion {{ x }} + {{ y }} free', ['{{ x }}' => 3, '{{ y }}' => 1], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $expectedBrandFilterOptions = [
            [
                'brand' => [
                    'name' => t('A4tech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'brand' => [
                    'name' => t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'brand' => [
                    'name' => t('Philips', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'brand' => [
                    'name' => t('Samsung', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'brand' => [
                    'name' => t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $expectedParameterFilterOptions = [
            [
                'isCollapsed' => false,
                'name' => t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterColorFilterOption',
                'unit' => null,
                'values' => $colors,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 1,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 1,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => true,
                'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => $hdmiValues,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => $materials,
            ],
            [
                'isCollapsed' => true,
                'name' => t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => null,
                'minimalValue' => 5.0,
                'maximalValue' => 5.0,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 3,
                        'isAbsolute' => true,
                    ],
                    [
                        'text' => t('3840×2160 (4K UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 1,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 27.0,
                'maximalValue' => 55.0,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 1,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 4,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 4,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Warranty', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 1.0,
                'maximalValue' => 5.0,
            ],
        ];


        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryProductsFilterOptions.graphql', [
            'categoryUuid' => $category->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame(4, $data['products']['productFilterOptions']['inStock']);
        $this->assertSame($minimalPrice, $data['products']['productFilterOptions']['minimalPrice']);
        $this->assertSame($maximalPrice, $data['products']['productFilterOptions']['maximalPrice']);

        $this->assertArrayElements($expectedFlagFilterOptions, $data['products']['productFilterOptions']['flags']);
        $this->assertArrayElements($expectedBrandFilterOptions, $data['products']['productFilterOptions']['brands']);
        $this->assertEquals($expectedParameterFilterOptions, $data['products']['productFilterOptions']['parameters']);
    }

    public function testGetElectronicsBrandFilterOptionsWithAppliedFilter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $brandA4tech = $this->getReference(BrandDataFixture::BRAND_A4TECH, Brand::class);

        $expectedBrandFilterOptions = [
            [
                'brand' => [
                    'name' => t('A4tech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 0,
                'isAbsolute' => false,
            ],
            [
                'brand' => [
                    'name' => t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => false,
            ],
            [
                'brand' => [
                    'name' => t('Philips', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => false,
            ],
            [
                'brand' => [
                    'name' => t('Samsung', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => false,
            ],
            [
                'brand' => [
                    'name' => t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 1,
                'isAbsolute' => false,
            ],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryProductsFilterOptions.graphql', [
            'categoryUuid' => $category->getUuid(),
            'filter' => ['brands' => [$brandA4tech->getUuid()]],
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertArrayElements($expectedBrandFilterOptions, $data['products']['productFilterOptions']['brands']);
    }

    public function testGetElectronicsFlagFilterOptionsWithAppliedFilters(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $flagAction = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class);

        $expectedFlagFilterOptions = [
            [
                'flag' => [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 0,
                'isAbsolute' => false,
            ],
            [
                'flag' => [
                    'name' => t('Promotion {{ x }} + {{ y }} free', ['{{ x }}' => 3, '{{ y }}' => 1], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 0,
                'isAbsolute' => false,
            ],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryProductsFilterOptions.graphql', [
            'categoryUuid' => $category->getUuid(),
            'filter' => ['flags' => [$flagAction->getUuid()]],
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertArrayElements($expectedFlagFilterOptions, $data['products']['productFilterOptions']['flags']);
    }

    public function testGetElectronicsParametersFilterOptionsWithAppliedFilter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $parameterFacade = self::getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_HDMI);

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
            $this->firstDomainLocale,
        );

        $materials = [
            [
                'text' => t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 0,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $materials, $this->getLocaleForFirstDomain());

        $colors = [
            [
                'text' => t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
                'rgbHex' => '#000000',
                'colorIcon' => null,
            ],
            [
                'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
                'rgbHex' => '#ff0000',
                'colorIcon' => $this->getRedColorExpectedFile(),
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $colors, $this->getLocaleForFirstDomain());

        $screenSizes = [
            [
                'text' => '27',
                'count' => 0,
                'isAbsolute' => true,
            ],
            [
                'text' => '30',
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => '47',
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $screenSizes, $this->getLocaleForFirstDomain());

        $hdmiValues = [
            [
                'text' => t(
                    'No',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->firstDomainLocale,
                ),
                'count' => 0,
                'isAbsolute' => false,
            ],
            [
                'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 2,
                'isAbsolute' => false,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $hdmiValues, $this->getLocaleForFirstDomain());

        $expectedArray = [
            [
                'isCollapsed' => false,
                'name' => t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => $materials,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterColorFilterOption',
                'unit' => null,
                'values' => $colors,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 0,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => true,
                'name' => t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => null,
                'minimalValue' => 5.0,
                'maximalValue' => 5.0,
            ],
            [
                'isCollapsed' => false,
                'name' => t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 0,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 27.0,
                'maximalValue' => 55.0,
            ],
            [
                'isCollapsed' => true,
                'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => $hdmiValues,
            ],
            [
                'isCollapsed' => false,
                'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 2,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 2,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 0,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t(
                            '1920×1080 (Full HD)',
                            [],
                            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                            $this->firstDomainLocale,
                        ),
                        'count' => 2,
                        'isAbsolute' => true,
                    ],
                    [
                        'text' => t(
                            '3840×2160 (4K UHD)',
                            [],
                            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                            $this->firstDomainLocale,
                        ),
                        'count' => 0,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Warranty', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 1.0,
                'maximalValue' => 5.0,
            ],
        ];

        $result = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryProductsFilterOptions.graphql', [
            'categoryUuid' => $category->getUuid(),
            'filter' => [
                'parameters' => [
                    [
                        'parameter' => $parameter->getUuid(),
                        'values' => [$parameterValue->getUuid()],
                    ],
                ],
            ],
        ]);
        $data = $this->getResponseDataForGraphQlType($result, 'category');

        $this->assertArrayElements($expectedArray, $data['products']['productFilterOptions']['parameters']);
    }

    public function testGetProductFilterOptionsForSencorSearch(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('3499');
        $maximalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('7258.79');

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductsSearchFilterOptions.graphql', [
            'search' => 'sencor',
            'userIdentifier' => $userIdentifier,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'productsSearch');

        $this->assertSame($minimalPrice, $data['productFilterOptions']['minimalPrice']);
        $this->assertSame($maximalPrice, $data['productFilterOptions']['maximalPrice']);
        $this->assertSame(3, $data['productFilterOptions']['inStock']);
        $this->assertSame([
            [
                'count' => 2,
                'flag' => [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                ],
            ],
        ], $data['productFilterOptions']['flags']);
        $this->assertSame([
            [
                'count' => 3,
                'brand' => [
                    'name' => 'Sencor',
                ],
            ],
        ], $data['productFilterOptions']['brands']);
        $this->assertNull($data['productFilterOptions']['parameters']);
    }

    public function testSliderParameterFilterOptions(): void
    {
        $parameterSliderWarranty = $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class);
        $parameterSliderWarrantyUuid = $parameterSliderWarranty->getUuid();

        $translatedName = t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $slug = $this->transformStringHelper->stringToFriendlyUrlSlug($translatedName);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SliderFilterInCategory.graphql', [
            'urlSlug' => $slug,
        ]);

        $result = $this->getResponseDataForGraphQlType($response, 'category');
        $parameters = $result['products']['productFilterOptions']['parameters'];

        foreach ($parameters as $parameterArray) {
            if ($parameterArray['uuid'] === $parameterSliderWarrantyUuid) {
                $this->assertSame(1.0, $parameterArray['minimalValue']);
                $this->assertSame(5.0, $parameterArray['maximalValue']);
            }
        }
    }

    #[DataProvider('isSliderSelectable')]
    public function testIsSliderSelectable(bool $isSliderSelectable, array $filter): void
    {
        $parameterSliderWarranty = $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class);
        $parameterSliderWarrantyUuid = $parameterSliderWarranty->getUuid();

        $translatedName = t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $slug = $this->transformStringHelper->stringToFriendlyUrlSlug($translatedName);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SliderFilterInCategory.graphql', [
            'urlSlug' => $slug,
            'filter' => $filter,
        ]);

        $result = $this->getResponseDataForGraphQlType($response, 'category');
        $parameters = $result['products']['productFilterOptions']['parameters'];

        foreach ($parameters as $parameterArray) {
            if ($parameterArray['uuid'] === $parameterSliderWarrantyUuid) {
                $this->assertSame(1.0, $parameterArray['minimalValue']);
                $this->assertSame(5.0, $parameterArray['maximalValue']);
            }
        }
    }

    public static function isSliderSelectable(): iterable
    {
        yield [true, 'filter' => []];

        yield [false, 'filter' => ['brands' => ['738ead90-3108-433d-ad6e-1ea23f68a13d']]];
    }

    private function getRedColorExpectedFile(): array
    {
        $redColorParameterValue = $this->getReference(ParameterColorValueDataFixture::PARAMETER_VALUE_RED_REFERENCE_PREFIX . $this->firstDomainLocale, ParameterValue::class);
        $allFilesArray = $this->getFilesByEntity($redColorParameterValue);

        return array_first($allFilesArray);
    }
}
