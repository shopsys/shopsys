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
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductsFilteringTest extends ProductsGraphQlTestCase
{
    private const PARAMETER_NUMBER_OF_BUTTONS_ID = 9;

    private string $firstDomainLocale;

    public function setUp(): void
    {
        parent::setUp();

        $this->firstDomainLocale = $this->getLocaleForFirstDomain();
    }

    public function testFilterByBrand(): void
    {
        $brand = $this->getReference(BrandDataFixture::BRAND_APPLE, Brand::class);

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
            ['name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
    }

    public function testFilterByFlag(): void
    {
        $flag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class);

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
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
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
            ['name' => t('OKI MC861cdxm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
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
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->firstDomainLocale,
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
            ['name' => t('D-link', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Samsung Galaxy Core 2 (SM-G355) - black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Samsung Galaxy Core Plus (SM-G350) - white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('HTC Desire 816 White', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Million-euro toilet paper', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Pot holder, black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t(
                'Reflective tape for safe movement on the road',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->firstDomainLocale,
            )],
        ];

        $this->assertProducts($query, 'products', $productsNotExpected, false);
    }

    public function testFilterByParameter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $parameterFacade = self::getContainer()->get(ParameterFacade::class);
        $parameter = $parameterFacade->getById(self::PARAMETER_NUMBER_OF_BUTTONS_ID);

        $parameterValue = $parameterFacade->getParameterValueByValueTextAndLocale(
            t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale),
            $this->firstDomainLocale,
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
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->firstDomainLocale,
            )],
        ];

        $this->assertProducts($query, 'category', $productsExpected);
    }

    public function testFilterBySliderParameter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $parameterSlider = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY, Parameter::class);

        $query = '
            query {
                category (uuid: "' . $category->getUuid() . '") {
                    products (
                        filter: {
                            parameters: [
                                {
                                    parameter: "' . $parameterSlider->getUuid() . '",
                                    minimalValue: 3
                                    maximalValue: 4
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
            ['name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Genius NetScroll 310 silver', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('Genius SlimStar i820', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
            ['name' => t('OKI MC861cdxn+ (01318206)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->firstDomainLocale)],
        ];

        $this->assertProducts($query, 'category', $productsExpected);
    }
}
