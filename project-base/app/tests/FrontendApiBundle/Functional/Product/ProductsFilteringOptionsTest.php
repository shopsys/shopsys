<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
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
        $query = $this->getElectronicsQuery();

        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('319');
        } else {
            $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('318.85');
        }

        $maximalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('9889.9');

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
                'count' => 1,
                'isAbsolute' => true,
                'rgbHex' => '#000000',
            ],
            [
                'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 3,
                'isAbsolute' => true,
                'rgbHex' => '#ff0000',
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
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('text', $hdmiValues, $this->getLocaleForFirstDomain());

        $expectedFlagFilterOptions = [
            [
                'flag' => [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'count' => 2,
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
                'minimalValue' => 5,
                'maximalValue' => 5,
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
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 27,
                'maximalValue' => 47,
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
                        'count' => 3,
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
                        'count' => 3,
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
                'minimalValue' => 1,
                'maximalValue' => 5,
            ],
        ];


        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame(3, $data['products']['productFilterOptions']['inStock']);
        $this->assertSame($minimalPrice, $data['products']['productFilterOptions']['minimalPrice']);
        $this->assertSame($maximalPrice, $data['products']['productFilterOptions']['maximalPrice']);

        $this->assertArrayElements($expectedFlagFilterOptions, $data['products']['productFilterOptions']['flags']);
        $this->assertArrayElements($expectedBrandFilterOptions, $data['products']['productFilterOptions']['brands']);
        $this->assertSame($expectedParameterFilterOptions, $data['products']['productFilterOptions']['parameters']);
    }

    public function testGetElectronicsBrandFilterOptionsWithAppliedFilter(): void
    {
        $brandA4tech = $this->getReference(BrandDataFixture::BRAND_A4TECH, Brand::class);

        $query = $this->getElectronicsQuery('{ brands: ["' . $brandA4tech->getUuid() . '"] }');

        $expectedJson = '[
{
    "brand": {
        "name": "' . t('A4tech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
    },
    "count": 0,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
    },
    "count": 1,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('Philips', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
    },
    "count": 1,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
    },
    "count": 1,
    "isAbsolute": false
}]';

        $result = $this->getResponseContentForQuery($query);
        $resultJson = json_encode($result['data']['category']['products']['productFilterOptions']['brands']);

        $this->assertJsonStringEqualsJsonString($expectedJson, $resultJson);
    }

    public function testGetElectronicsFlagFilterOptionsWithAppliedFilters(): void
    {
        $flagAction = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class);

        $query = $this->getElectronicsQuery('{ flags: ["' . $flagAction->getUuid() . '"] }');

        $expectedJson = '[
    {
        "flag": {
            "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
        },
        "count": 0,
        "isAbsolute": false
    }
]';

        $result = $this->getResponseContentForQuery($query);
        $resultJson = json_encode($result['data']['category']['products']['productFilterOptions']['flags']);

        $this->assertJsonStringEqualsJsonString($expectedJson, $resultJson);
    }

    public function testGetElectronicsParametersFilterOptionsWithAppliedFilter(): void
    {
        $parameterFacade = self::getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_HDMI);

        $parameterValue = $parameterFacade->getParameterValueByValueTextNumericValueAndLocale(
            t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
            null,
            $this->firstDomainLocale,
        );

        $query = $this->getElectronicsQuery('{
            parameters: [ {
                parameter: "' . $parameter->getUuid() . '",
                values: [ "' . $parameterValue->getUuid() . '" ]
            }]
        }');

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
            ],
            [
                'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
                'rgbHex' => '#ff0000',
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
                'count' => 1,
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
                'minimalValue' => 5,
                'maximalValue' => 5,
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
                'minimalValue' => 27,
                'maximalValue' => 47,
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
                ],
            ],
            [
                'isCollapsed' => false,
                'name' => t('Warranty', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => [
                    'name' => t('years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                ],
                'minimalValue' => 1,
                'maximalValue' => 5,
            ],
        ];

        $result = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($result, 'category');

        $this->assertArrayElements($expectedArray, $data['products']['productFilterOptions']['parameters']);
    }

    /**
     * @param string|null $filter
     * @return string
     */
    private function getElectronicsQuery(?string $filter = null): string
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        if ($filter !== null) {
            $filter = '(filter: ' . $filter . ')';
        }

        return '
            query {
                category (uuid: "' . $category->getUuid() . '") {
                    products ' . $filter . ' {
                        productFilterOptions {
                            flags {
                                flag {
                                    name
                                }
                                count
                                isAbsolute
                            },
                            brands {
                                brand {
                                    name
                                }
                                count
                                isAbsolute
                            },
                            inStock,
                            minimalPrice,
                            maximalPrice,
                            parameters {
                                isCollapsed
                                name
                                __typename
                                unit {
                                    name
                                }
                                ... on ParameterCheckboxFilterOption {
                                    values {
                                        text
                                        count
                                        isAbsolute
                                    }
                                }
                                ... on ParameterColorFilterOption {
                                    values {
                                        text
                                        count
                                        isAbsolute
                                        rgbHex
                                    }
                                }
                                ... on ParameterSliderFilterOption {
                                    minimalValue
                                    maximalValue
                                }
                            }
                        }
                    },
                }
            }
        ';
    }

    public function testGetProductFilterOptionsForSencorSearch()
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $query = 'query {
          productsSearch (searchInput: { search: "sencor", isAutocomplete: false, userIdentifier: "' . $userIdentifier . '"}) {
            productFilterOptions {
              minimalPrice
              maximalPrice
              inStock
              flags {
                count
                flag {
                  name
                }
              }
              brands {
                count
                brand {
                  name
                }
              }
              parameters {
                name
              }
            }
          }
        }';

        $minimalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('3499');
        $maximalPrice = $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('7258.79');

        $expectedResult = '{
          "data": {
            "productsSearch": {
              "productFilterOptions": {
                "minimalPrice": "' . $minimalPrice . '",
                "maximalPrice": "' . $maximalPrice . '",
                "inStock": 3,
                "flags": [
                  {
                    "count": 2,
                    "flag": {
                      "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                    }
                  }
                ],
                "brands": [
                  {
                    "count": 3,
                    "brand": {
                      "name": "Sencor"
                    }
                  }
                ],
                "parameters": null
              }
            }
          }
        }';

        $this->assertQueryWithExpectedJson($query, $expectedResult);
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
                $this->assertSame(1, $parameterArray['minimalValue']);
                $this->assertSame(5, $parameterArray['maximalValue']);
            }
        }
    }

    /**
     * @param bool $isSliderSelectable
     * @param array $filter
     */
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
                $this->assertSame(1, $parameterArray['minimalValue']);
                $this->assertSame(5, $parameterArray['maximalValue']);
            }
        }
    }

    /**
     * @return iterable
     */
    public static function isSliderSelectable(): iterable
    {
        yield [true, 'filter' => []];

        yield [false, 'filter' => ['brands' => ['738ead90-3108-433d-ad6e-1ea23f68a13d']]];
    }
}
