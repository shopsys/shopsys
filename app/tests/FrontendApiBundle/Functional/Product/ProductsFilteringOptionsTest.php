<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsFilteringOptionsTest extends GraphQlTestCase
{
    private const PARAMETER_HDMI = 5;

    /**
     * @var string
     */
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
                                "name": "' . t('Action', [], 'dataFixtures', $this->firstDomainLocale) . '"
                            },
                            "count": 2,
                            "isAbsolute": true
                        }
                    ],
                    "brands": [
                        {
                            "brand": {
                                "name": "' . t('A4tech', [], 'dataFixtures', $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('LG', [], 'dataFixtures', $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('Philips', [], 'dataFixtures', $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "brand": {
                                "name": "' . t('Sencor', [], 'dataFixtures', $this->firstDomainLocale) . '"
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
                            "name": "' . t('Materiál', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t(
            'dřevo',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t(
            'kov',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t(
            'plast',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Color', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterColorFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t(
            'černá',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": "#000000"
                                },
                                {
                                    "text": "' . t(
            'červená',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 3,
                                    "isAbsolute": true,
                                    "rgbHex": "#ff0000"
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t(
            'Windows 2000/XP/Vista/7',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t(
            'Right-handed',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": true,
                            "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": {
                                "name": "in"
                            },
                            "values": [
                                {
                                    "text": "' . t('27\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('30\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('47\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": true,
                            "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,                        
                            "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "isCollapsed": false,
                            "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "__typename": "ParameterCheckboxFilterOption",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t(
            '1920×1080 (Full HD)',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
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
        "name": "' . t('A4tech', [], 'dataFixtures', $this->firstDomainLocale) . '"
    },
    "count": 0,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('LG', [], 'dataFixtures', $this->firstDomainLocale) . '"
    },
    "count": 1,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('Philips', [], 'dataFixtures', $this->firstDomainLocale) . '"
    },
    "count": 1,
    "isAbsolute": false
},
{
    "brand": {
        "name": "' . t('Sencor', [], 'dataFixtures', $this->firstDomainLocale) . '"
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
            "name": "' . t('Action', [], 'dataFixtures', $this->firstDomainLocale) . '"
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
        $parameterFacade = $this->getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_HDMI);

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('No', [], 'dataFixtures', $this->firstDomainLocale),
            $this->firstDomainLocale
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
        "name": "' . t('Materiál', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            'dřevo',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 0,
                "isAbsolute": true
            },
            {
                "text": "' . t(
            'kov',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true
            },
            {
                "text": "' . t(
            'plast',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,    
        "name": "' . t('Color', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterColorFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            'černá',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": "#000000"
            },
            {
                "text": "' . t(
            'červená',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": "#ff0000"
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            'Windows 2000/XP/Vista/7',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            'Right-handed',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": true,
        "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": {
            "name": "in"
        },
        "values": [
            {
                "text": "' . t('27\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            },
            {
                "text": "' . t('30\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            },
            {
                "text": "' . t('47\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": true,
        "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": false
            },
            {
                "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": false
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "isCollapsed": false,
        "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "__typename": "ParameterCheckboxFilterOption",
        "unit": null,
        "values": [
            {
                "text": "' . t(
            '1920×1080 (Full HD)',
            [],
            'dataFixtures',
            $this->firstDomainLocale
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
                      "name": "' . t('Akce', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
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
        $query = 'query {
          category(urlSlug: "televize-audio") {
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
                        "name": "' . t('LG', [], 'dataFixtures', $this->firstDomainLocale) . '"
                      }
                    }
                  ],
                  "parameters": [
                    {
                      "isCollapsed": false,
                      "name": "' . t('Material', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('plastic', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Color', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('red', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('Ano', [], 'messages', $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Rozlišení', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('1366×768 (HD Ready)', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        },
                        {
                          "text": "' . t('1920×1080 (Full HD)', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Úhlopříčka', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('47\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        },
                        {
                          "text": "' . t('60\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('Ne', [], 'messages', $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
                      "isCollapsed": false,
                      "name": "' . t('Technologie', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
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

        $query = 'query {
          flag(urlSlug: "akce") {
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
                        "name": "' . t('Akce', [], 'dataFixtures', $this->firstDomainLocale) . '"
                      }
                    }
                  ],
                  "brands": [
                    {
                      "count": 1,
                      "brand": {
                        "name": "' . t('Sencor', [], 'dataFixtures', $this->firstDomainLocale) . '"
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
                        "name": "' . t('Akce', [], 'dataFixtures', $this->firstDomainLocale) . '"
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

        $query = 'query {
          category(urlSlug: "/pocitace-prislusenstvi") {    
            products {
              productFilterOptions {
                parameters {
                  uuid
                  ... on ParameterSliderFilterOption {
                      minimalValue
                      maximalValue
                  }
                }
              }
            }
          }
        }
        ';

        $result = $this->getResponseDataForGraphQlType($this->getResponseContentForQuery($query), 'category');
        $parameters = $result['products']['productFilterOptions']['parameters'];
        foreach ($parameters as $parameterArray) {
            if ($parameterArray['uuid'] === $parameterSliderWarrantyUuid) {
                $this->assertSame(1, $parameterArray['minimalValue']);
                $this->assertSame(5, $parameterArray['maximalValue']);
            }
        }
    }
}
