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

        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('318.75');
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
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "flag": {
                                "name": "' . t('TOP', [], 'dataFixtures', $this->firstDomainLocale) . '"
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
                            "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
                            "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
                        },
                        {
                            "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
                            "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
                            "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
        $flagAction = $this->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT);

        $query = $this->getElectronicsQuery('{ flags: ["' . $flagAction->getUuid() . '"] }');

        $expectedJson = '[
    {
        "flag": {
            "name": "' . t('Action', [], 'dataFixtures', $this->firstDomainLocale) . '"
        },
        "count": 0,
        "isAbsolute": false
    },
    {
        "flag": {
            "name": "' . t('TOP', [], 'dataFixtures', $this->firstDomainLocale) . '"
        },
        "count": 1,
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
        "name": "' . t('Ergonomics', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
        "name": "' . t('Gaming mouse', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('HDMI', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('No', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": false
            },
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 1,
                "isAbsolute": false
            }
        ]
    },
    {
        "name": "' . t('Number of buttons', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('5', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('Resolution', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
    },
    {
        "name": "' . t('Screen size', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
        "name": "' . t('Supported OS', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
        "name": "' . t('Technology', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('LED', [], 'dataFixtures', $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('USB', [], 'dataFixtures', $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('Yes', [], 'dataFixtures', $this->firstDomainLocale) . '",
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
                                name
                                values {
                                    text
                                    count
                                    isAbsolute
                                }
                            }
                        }
                    },
                }
            }
        ';
    }
}
