<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
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

        $expectedResult = '{
    "data": {
        "category": {
            "products": {
                "productFilterOptions": {
                    "flags": [
                        {
                            "flag": {
                                "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 2,
                            "isAbsolute": true
                        }
                    ],
                    "brands": [
                        {
                            "brand": {
                                "name": "' . t('A4tech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('Philips', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        }
                    ],
                    "inStock": 4,
                    "minimalPrice": "' . $minimalPrice . '",
                    "maximalPrice": "' . $maximalPrice . '",
                    "parameters": [
                        {
                            "isCollapsed": false,
                            "name": "' . t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t(
            'plastic',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale,
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterColorFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": "#000000"
                                },
                                {
                                    "text": "' . t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true,
                                    "rgbHex": "#ff0000"
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": true,
                            "name": "' . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": {
                                "name": "in"
                            },
                            "values": [
                                {
                                    "text": "' . t('27\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('30\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('47\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": true,
                            "name": "' . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t(
            'Yes',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale,
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,                        
                            "name": "' . t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        }
                    ]
                }
            }
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $expectedResult);
    }

    public function testGetElectronicsBrandFilterOptionsWithAppliedFilter(): void
    {
        $brandA4tech = $this->getReference(BrandDataFixture::BRAND_A4TECH);

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
        $flagAction = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);

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

        $expectedJson = '[
    {
        "isCollapsed": false,
        "name": "' . t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            },
            {
                "text": "' . t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            },
            {
                "text": "' . t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,    
        "name": "' . t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterColorFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": "#000000"
            },
            {
                "text": "' . t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": "#ff0000"
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": true,
        "name": "' . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": {
            "name": "in"
        },
        "values": [
            {
                "text": "' . t('27\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            },
            {
                "text": "' . t('30\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            },
            {
                "text": "' . t('47\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": true,
        "name": "' . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            'No',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale,
        ) . '",
                "count": 0,
                "isAbsolute": false
            },
            {
                "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": false
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            '1920×1080 (Full HD)',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale,
        ) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    }
]';

        $result = $this->getResponseContentForQuery($query);

        $resultJson = json_encode($result['data']['category']['products']['productFilterOptions']['parameters']);

        $this->assertJsonStringEqualsJsonString($expectedJson, $resultJson);
    }

    /**
     * @param string|null $filter
     * @return string
     */
    private function getElectronicsQuery(?string $filter = null): string
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

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
        $query = 'query {
          products (search:"sencor") {
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
            "products": {
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

    public function testGetProductFilterOptionsForSearchInCategory(): void
    {
        $translatedName = t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $slug = TransformString::stringToFriendlyUrlSlug($translatedName);

        $query = 'query {
          category(urlSlug: "' . $slug . '") {
            products(search: "FHD") {
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
                  isCollapsed
                  name
                  ...on ParameterCheckboxFilterOption {
                    values {text count}
                  }
                  ...on ParameterColorFilterOption {
                    values {text count}
                  }               
                }
              }
            }
          }
        }';

        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('21590');
        $maximalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('24010');

        $expectedResult = '{
          "data": {
            "category": {
              "products": {
                "productFilterOptions": {
                  "minimalPrice": "' . $minimalPrice . '",
                  "maximalPrice": "' . $maximalPrice . '",
                  "inStock": 2,
                  "flags": null,
                  "brands": [
                    {
                      "count": 2,
                      "brand": {
                        "name": "' . t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                      }
                    }
                  ],
                  "parameters": [
                    {
                      "isCollapsed": false,
                      "name": "' . t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('Yes', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        },
                        {
                          "text": "' . t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('47\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        },
                        {
                          "text": "' . t('60\"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('No', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    }
                  ]
                }
              }
            }
          }
        }';

        $this->assertQueryWithExpectedJson($query, $expectedResult);
    }

    public function testGetProductFilterOptionsForSearchWhenListingByFlag(): void
    {
        $price = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('3499');

        $translatedName = t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $slug = TransformString::stringToFriendlyUrlSlug($translatedName);

        $query = 'query {
          flag(urlSlug: "' . $slug . '") {
            products(search: "Hello") {      
              productFilterOptions {
                minimalPrice
                maximalPrice
                inStock
                flags {
                  count
                  isAbsolute
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
              }
            }
          }
        }';
        $expectedResult = '{
          "data": {
            "flag": {
              "products": {
                "productFilterOptions": {
                  "minimalPrice": "' . $price . '",
                  "maximalPrice": "' . $price . '",
                  "inStock": 1,
                  "flags": [
                    {
                      "count": 0,
                      "isAbsolute": false,
                      "flag": {
                        "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                      }
                    }
                  ],
                  "brands": [
                    {
                      "count": 1,
                      "brand": {
                        "name": "' . t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                      }
                    }
                  ]
                }
              }
            }
          }
        }';
        $this->assertQueryWithExpectedJson($query, $expectedResult);
    }

    public function testGetProductFilterOptionsForSearchWhenListingByBrand(): void
    {
        $price = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('3499');

        $query = 'query {
          brand(urlSlug: "sencor") {
            products(search:"Hello") {    
              productFilterOptions {
                minimalPrice
                maximalPrice
                inStock
                flags {
                  count
                  isAbsolute
                  flag {
                    name
                  }
                }
              }
            }
          }
        }';

        $expectedResult = '{
          "data": {
            "brand": {
              "products": {
                "productFilterOptions": {
                  "minimalPrice": "' . $price . '",
                  "maximalPrice": "' . $price . '",
                  "inStock": 1,
                  "flags": [
                    {
                      "count": 1,
                      "isAbsolute": true,
                      "flag": {
                        "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                      }
                    }
                  ]
                }
              }
            }
          }
        }';

        $this->assertQueryWithExpectedJson($query, $expectedResult);
    }

    public function testSliderParameterFilterOptions(): void
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameterSliderWarranty */
        $parameterSliderWarranty = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);
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
     * @dataProvider isSliderSelectable
     * @param bool $isSliderSelectable
     * @param array $filter
     */
    public function testIsSliderSelectable(bool $isSliderSelectable, array $filter): void
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameterSliderWarranty */
        $parameterSliderWarranty = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);
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
    public function isSliderSelectable(): iterable
    {
        yield [true, 'filter' => []];

        yield [false, 'filter' => ['brands' => ['738ead90-3108-433d-ad6e-1ea23f68a13d']]];
    }
}
