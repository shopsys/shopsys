<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;

class ProductOnCurrentDomainElasticFacadeCountDataTest extends ParameterTransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade;

    /**
     * @inject
     */
    protected ProductFilterConfigFactory $productFilterConfigFactory;

    /**
     * @inject
     */
    protected ParameterRepository $parameterRepository;

    /**
     * @inject
     */
    protected PriceConverter $priceConverter;

    public function testCategory(): void
    {
        foreach ($this->categoryTestCasesProvider() as $testCaseName => $dataProvider) {
            /** @var \App\Model\Category\Category $category */
            $category = $dataProvider[0];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData */
            $filterData = $dataProvider[1];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData */
            $expectedCountData = $dataProvider[2];

            $filterConfig = $this->productFilterConfigFactory->createForCategory(
                $this->domain->getLocale(),
                $category,
            );
            $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataInCategory(
                $category->getId(),
                $filterConfig,
                $filterData,
            );
            $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData), 'TestCase: ' . $testCaseName);
        }
    }

    /**
     * @return array[]
     */
    public function categoryTestCasesProvider(): array
    {
        return [
            'no-filter' => $this->categoryNoFilterTestCase(),
            'one-flag' => $this->categoryOneFlagTestCase(),
            'price' => $this->categoryPriceTestCase(),
            'flag-brand-parameters' => $this->categoryFlagBrandAndParametersTestCase(),
            'parameters' => $this->categoryParametersTestCase(),
        ];
    }

    public function testSearch(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        foreach ($this->searchTestCasesProvider() as $dataProvider) {
            /** @var string $searchText */
            $searchText = $dataProvider[0];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData */
            $filterData = $dataProvider[1];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData */
            $expectedCountData = $dataProvider[2];

            $filterConfig = $this->productFilterConfigFactory->createForSearch(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $searchText,
            );
            $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch(
                $searchText,
                $filterConfig,
                $filterData,
            );

            $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData));
        }
    }

    /**
     * @return array[]
     */
    public function searchTestCasesProvider(): array
    {
        return [
            'no-filter' => $this->searchNoFilterTestCase(),
            'one-flag' => $this->searchOneFlagTestCase(),
            'one-brand' => $this->searchOneBrandTestCase(),
            'price' => $this->searchPriceTestCase(),
            'stock' => $this->searchStockTestCase(),
            'price-stock-flag-brands' => $this->searchPriceStockFlagBrandsTestCase(),
        ];
    }

    /**
     * @return array
     */
    private function categoryNoFilterTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);
        $filterData = new ProductFilterData();
        $countData = new ProductFilterCountData();

        $countData->countInStock = 7;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 4,
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId() => 2,
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId() => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR_PRINTING, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            $this->getReference(ParameterDataFixture::PARAM_DIMENSIONS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 5,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_LCD, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 3,
            ],
            $this->getReference(ParameterDataFixture::PARAM_MAXIMUM_SIZE, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('A3') => 5,
                $this->getParameterValueIdForFirstDomain('A4') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_RESOLUTION, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 5,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_TECHNOLOGY, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 7,
            ],
            $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('5400') => 1,
                $this->getParameterValueIdForFirstDomain('3500') => 6,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WIFI, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 5,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4') => 2,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function categoryOneFlagTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);
        $filterData = new ProductFilterData();
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class);

        $countData = new ProductFilterCountData();

        $countData->countInStock = 2;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
        ];
        $countData->countByParameterIdAndValueId = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR_PRINTING, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_DIMENSIONS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 1,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_LCD, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_MAXIMUM_SIZE, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('A3') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_RESOLUTION, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_TECHNOLOGY, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('5400') => 1,
                $this->getParameterValueIdForFirstDomain('3500') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WIFI, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 1,
                $this->getParameterValueIdForFirstDomain('No') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4') => 2,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function categoryPriceTestCase(): array
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);

        $filterData = new ProductFilterData();
        $filterData->minimalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(1000)->multiply('1.21'),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(80000)->multiply('1.21'),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );

        $countData = new ProductFilterCountData();

        $countData->countInStock = 6;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 3,
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId() => 2,
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId() => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR_PRINTING, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            $this->getReference(ParameterDataFixture::PARAM_DIMENSIONS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 4,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_LCD, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_MAXIMUM_SIZE, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('A3') => 5,
                $this->getParameterValueIdForFirstDomain('A4') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_RESOLUTION, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 4,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_TECHNOLOGY, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 6,
            ],
            $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('3500') => 5,
                $this->getParameterValueIdForFirstDomain('5400') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WIFI, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('4') => 2,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function categoryFlagBrandAndParametersTestCase(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)], [$firstDomainLocale => t(
                '4800x1200',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            )]],
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('3500', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );

        $countData = new ProductFilterCountData();

        $countData->countInStock = 0;
        $countData->countByBrandId = [];
        $countData->countByFlagId = [];
        $countData->countByParameterIdAndValueId = [
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('5400') => 1,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function categoryParametersTestCase(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);
        $filterData = new ProductFilterData();
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)], [$firstDomainLocale => t(
                '4800x1200',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            )]],
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('3500', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );

        $countData = new ProductFilterCountData();

        $countData->countInStock = 4;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 3,
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [];
        $countData->countByParameterIdAndValueId = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR_PRINTING, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            $this->getReference(ParameterDataFixture::PARAM_DIMENSIONS, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 4,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_LCD, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_MAXIMUM_SIZE, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('A3') => 2,
                $this->getParameterValueIdForFirstDomain('A4') => 2,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_RESOLUTION, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('2400x600') => 4,
            ],
            $this->getReference(ParameterDataFixture::PARAM_PRINT_TECHNOLOGY, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 4,
            ],
            $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('3500') => 4,
                $this->getParameterValueIdForFirstDomain('5400') => 1,
            ],
            $this->getReference(ParameterDataFixture::PARAM_WIFI, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @param array $namesByLocale
     * @param array $valuesTextsByLocales
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData
     */
    private function createParameterFilterData(array $namesByLocale, array $valuesTextsByLocales)
    {
        $parameter = $this->parameterRepository->findParameterByNames($namesByLocale);
        $parameterValues = $this->getParameterValuesByLocalesAndTexts($valuesTextsByLocales);

        $parameterFilterData = new ParameterFilterData();
        $parameterFilterData->parameter = $parameter;
        $parameterFilterData->values = $parameterValues;

        return $parameterFilterData;
    }

    /**
     * @param array[] $valuesTextsByLocales
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    private function getParameterValuesByLocalesAndTexts(array $valuesTextsByLocales)
    {
        $parameterValues = [];

        foreach ($valuesTextsByLocales as $valueTextsByLocales) {
            foreach ($valueTextsByLocales as $locale => $text) {
                $parameterValue = $this->em->getRepository(ParameterValue::class)->findOneBy([
                    'text' => $text,
                    'locale' => $locale,
                ]);
                $parameterValues[] = $parameterValue;
            }
        }

        return $parameterValues;
    }

    /**
     * @return array
     */
    private function searchNoFilterTestCase(): array
    {
        $filterData = new ProductFilterData();
        $countData = new ProductFilterCountData();
        $countData->countInStock = 5;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 4,
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId() => 1,
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId() => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function searchOneFlagTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);
        $countData = new ProductFilterCountData();
        $countData->countInStock = 1;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function searchOneBrandTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class);
        $countData = new ProductFilterCountData();

        $countData->countInStock = 4;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId() => 1,
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId() => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function searchPriceTestCase(): array
    {
        $filterData = new ProductFilterData();
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $filterData->minimalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(5000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(50000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );
        $countData = new ProductFilterCountData();
        $countData->countInStock = 2;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 2,
        ];
        $countData->countByFlagId = [];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function searchStockTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $countData = new ProductFilterCountData();
        $countData->countInStock = 5;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 4,
            $this->getReference(BrandDataFixture::BRAND_HP, Brand::class)->getId() => 1,
        ];
        $countData->countByFlagId = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId() => 1,
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId() => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return array
     */
    private function searchPriceStockFlagBrandsTestCase(): array
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DELONGHI, Brand::class);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DEFENDER, Brand::class);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_GENIUS, Brand::class);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_HP, Brand::class);
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(20000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );

        $countData = new ProductFilterCountData();
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId() => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $countData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    private function removeEmptyParameters(ProductFilterCountData $countData): ProductFilterCountData
    {
        $result = clone $countData;

        foreach ($countData->countByParameterIdAndValueId as $parameterId => $values) {
            if (count($values) === 0) {
                unset($result->countByParameterIdAndValueId[$parameterId]);
            }
        }

        return $result;
    }
}
