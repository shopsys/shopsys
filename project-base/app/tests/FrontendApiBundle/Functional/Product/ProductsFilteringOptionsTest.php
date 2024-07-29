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
use App\Model\Product\Parameter\Parameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsFilteringOptionsTest extends GraphQlTestCase
{
    private const PARAMETER_HDMI = 5;

    private string $firstDomainLocale;

    public function setUp(): void
    {
        parent::setUp();

        $this->firstDomainLocale = $this->getLocaleForFirstDomain();
    }

    public function testGetElectronicsFilterOptions(): void
    {
        $query = $this->getElectronicsQuery();

        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('319');
        $maximalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('21590');

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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $materials, $this->getLocaleForFirstDomain());

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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $colors, $this->getLocaleForFirstDomain());

        $screenSizes = [
            [
                'text' => t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('47"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('text', $screenSizes, $this->getLocaleForFirstDomain());

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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $hdmiValues, $this->getLocaleForFirstDomain());

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
                'isCollapsed' => false,
                'name' => t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 1,
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
                        'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 3,
                        'isAbsolute' => true,
                    ],
                ],
            ],
            [
                'isCollapsed' => true,
                'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => [
                    'name' => 'in',
                ],
                'values' => $screenSizes,
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
                'name' => t('Warranty (in years)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => null,
            ],
        ];

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame(4, $data['products']['productFilterOptions']['inStock']);
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

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $materials, $this->getLocaleForFirstDomain());

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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $colors, $this->getLocaleForFirstDomain());

        $screenSizes = [
            [
                'text' => t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 0,
                'isAbsolute' => true,
            ],
            [
                'text' => t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
            [
                'text' => t('47"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                'count' => 1,
                'isAbsolute' => true,
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('text', $screenSizes, $this->getLocaleForFirstDomain());

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

        ArraySorter::sortArrayAlphabeticallyByValue('text', $hdmiValues, $this->getLocaleForFirstDomain());

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
                'isCollapsed' => false,
                'name' => t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => null,
                'values' => [
                    [
                        'text' => t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                        'count' => 0,
                        'isAbsolute' => true,
                    ],
                ],
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
                'isCollapsed' => true,
                'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterCheckboxFilterOption',
                'unit' => [
                    'name' => 'in',
                ],
                'values' => $screenSizes,
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
                'name' => t('Warranty (in years)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
                '__typename' => 'ParameterSliderFilterOption',
                'unit' => null,
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

        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('699');
        $maximalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('3499');

        $expectedResult = '{
          "data": {
            "productsSearch": {
              "productFilterOptions": {
                "minimalPrice": "' . $minimalPrice . '",
                "maximalPrice": "' . $maximalPrice . '",
                "inStock": 2,
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
                    "count": 2,
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
        $slug = TransformString::stringToFriendlyUrlSlug($translatedName);

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
        $slug = TransformString::stringToFriendlyUrlSlug($translatedName);

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
