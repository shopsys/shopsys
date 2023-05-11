<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
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
                                "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
                            },
                            "count": 1,
                            "isAbsolute": true
                        },
                        {
                            "flag": {
                                "name": "' . t('TOP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
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
                            "name": "' . t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t(
            'Right-handed',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 2,
                                    "isAbsolute": true
                                },
                                {
                                    "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t(
            '1920×1080 (Full HD)',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
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
                            "name": "' . t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t(
            'Windows 2000/XP/Vista/7',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                                    "count": 3,
                                    "isAbsolute": true
                                }
                            ]
                        },
                        {
                            "name": "' . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                            "values": [
                                {
                                    "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
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
        $flagAction = $this->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT);

        $query = $this->getElectronicsQuery('{ flags: ["' . $flagAction->getUuid() . '"] }');

        $expectedJson = '[
    {
        "flag": {
            "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
        },
        "count": 0,
        "isAbsolute": false
    },
    {
        "flag": {
            "name": "' . t('TOP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '"
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
        $parameterFacade = self::getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_HDMI);

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
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
        "name": "' . t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t(
            'Right-handed',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
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
        "name": "' . t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t(
            '1920×1080 (Full HD)',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
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
        "name": "' . t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t(
            'Windows 2000/XP/Vista/7',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->firstDomainLocale
        ) . '",
                "count": 0,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
                "count": 2,
                "isAbsolute": true
            }
        ]
    },
    {
        "name": "' . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
        "values": [
            {
                "text": "' . t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale) . '",
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
