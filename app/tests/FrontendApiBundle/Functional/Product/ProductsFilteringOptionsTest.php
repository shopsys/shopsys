<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
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
                            "name": "' . t('Materiál', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
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
                                    "isAbsolute": true,
                                    "rgbHex": null
                                },
                                {
                                    "text": "' . t(
            'kov',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 2,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                },
                                {
                                    "text": "' . t(
            'plast',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Color', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "colorPicker",
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
                            "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
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
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
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
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": {
                                "name": "in"
                            },
                            "values": [
                                {
                                    "text": "' . t('27\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                },
                                {
                                    "text": "' . t('30\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                },
                                {
                                    "text": "' . t('47\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                },
                                {
                                    "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
                            "unit": null,
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true,
                                    "rgbHex": null
                                }
                            ]
                        },
                        {
                            "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "type": "checkbox",
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
                                    "isAbsolute": true,
                                    "rgbHex": null
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
        "name": "' . t('Materiál', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
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
                "isAbsolute": true,
                "rgbHex": null
            },
            {
                "text": "' . t(
            'kov',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": null
            },
            {
                "text": "' . t(
            'plast',
            [],
            'dataFixtures',
            $this->firstDomainLocale
        ) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Color', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "colorPicker",
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
        "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
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
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": null,
        "values": [
            {
                "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
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
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": {
            "name": "in"
        },
        "values": [
            {
                "text": "' . t('27\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true,
                "rgbHex": null
            },
            {
                "text": "' . t('30\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": null
            },
            {
                "text": "' . t('47\"', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": false,
                "rgbHex": null
            },
            {
                "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": false,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": null,
        "values": [
            {
                "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
        "unit": null,
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true,
                "rgbHex": null
            }
        ]
    },
    {
        "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "type": "checkbox",
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
                "isAbsolute": true,
                "rgbHex": null
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
                                name
                                type
                                unit {
                                    name
                                }
                                values {
                                    text
                                    count
                                    isAbsolute
                                    rgbHex
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
                  name
                  values {text count}
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
                      "name": "' . t('Materiál', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "plast",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "name": "' . t('Barva', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('červená', [], 'dataFixtures', $this->firstDomainLocale) . '",
                          "count": 1
                        }
                      ]
                    },
                    {
                      "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('Ano', [], 'messages', $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
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
                      "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
                      "values": [
                        {
                          "text": "' . t('Ne', [], 'messages', $this->firstDomainLocale) . '",
                          "count": 2
                        }
                      ]
                    },
                    {
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
}
