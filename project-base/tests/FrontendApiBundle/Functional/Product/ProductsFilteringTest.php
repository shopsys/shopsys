<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsFilteringTest extends GraphQlTestCase
{
    private const PARAMETER_NUMBER_OF_BUTTONS_ID = 9;

    /**
     * @var string
     */
    private string $firstDomainLocale;

    public function setUp(): void
    {
        parent::setUp();

        $this->firstDomainLocale = $this->getLocaleForFirstDomain();
    }

    public function testFilterByBrand(): void
    {
        $brand = $this->getReference(BrandDataFixture::BRAND_APPLE);

        $query = '
            query {
                products (first: 1, filter: { brands: ["' . $brand->getUuid() . '"] }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t('Apple iPhone 5S 64GB, gold', [], 'dataFixtures', $this->firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
    }

    public function testFilterByFlag(): void
    {
        $flag = $this->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT);

        $query = '
            query {
                products (first: 1, filter: { flags: ["' . $flag->getUuid() . '"] }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
    }

    public function testFilterByMinimalPrice(): void
    {
        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('75000');

        $query = '
            query {
                products (first: 1, filter: { minimalPrice: "' . $minimalPrice . '" }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t('OKI MC861cdxm', [], 'dataFixtures', $this->firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
    }

    public function testFilterByMaximalPrice(): void
    {
        $maximalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('2500');

        $query = '
            query {
                products (last: 1, filter: { maximalPrice: "' . $maximalPrice . '" }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t(
                'ZN-8009 steam iron Ferrato stainless steel 2200 Watt Blue',
                [],
                'dataFixtures',
                $this->firstDomainLocale
            )],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
    }

    public function testFilterOnlyInStock(): void
    {
        $query = '
            query {
                products (first: 100, filter: { onlyInStock: true }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsNotExpected = [
            ['name' => t('D-link', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('Samsung Galaxy Core 2 (SM-G355) - black', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('Samsung Galaxy Core Plus (SM-G350) - white', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('Apple iPhone 5S 64GB, gold', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('HTC Desire 816 White', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('Million-euro toilet paper', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t('Pot holder, black', [], 'dataFixtures', $this->firstDomainLocale)],
            ['name' => t(
                'Reflective tape for safe movement on the road',
                [],
                'dataFixtures',
                $this->firstDomainLocale
            )],
        ];

        $this->assertProducts($query, 'products', $productsNotExpected, false);
    }

    public function testFilterByParameter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $parameterFacade = $this->getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_NUMBER_OF_BUTTONS_ID);

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('5', [], 'dataFixtures', $this->firstDomainLocale),
            $this->firstDomainLocale
        );

        $query = '
            query {
                category (uuid: "' . $category->getUuid() . '") {
                    products (
                        first: 1,
                        filter: {
                            parameters: [
                                {
                                    parameter: "' . $parameter->getUuid() . '",
                                    values: [
                                        "' . $parameterValue->getUuid() . '"
                                    ]
                                }
                            ]
                        }
                    ) {
                        edges {
                            node {
                                name
                            }
                        }
                    },
                }
            }
        ';

        $productsExpected = [
            ['name' => t(
                'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,',
                [],
                'dataFixtures',
                $this->firstDomainLocale
            )],
        ];

        $this->assertProducts($query, 'category', $productsExpected);
    }

    /**
     * @param string $query
     * @param string $graphQlType
     * @param array $products
     * @param bool $found
     */
    private function assertProducts(string $query, string $graphQlType, array $products, bool $found = true): void
    {
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        if ($graphQlType !== 'products') {
            $responseData = $responseData['products'];
        }

        $this->assertArrayHasKey('edges', $responseData);

        $queryResult = [];
        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $queryResult[] = $edge['node'];
        }

        if ($found === true) {
            $this->assertEquals($products, $queryResult, json_encode($queryResult));
        } else {
            $this->assertNotEquals($products, $queryResult, json_encode($queryResult));
        }
    }
}
