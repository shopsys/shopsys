<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory;
use App\Model\Product\Filter\ParameterFilterData;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;

abstract class ProductOnCurrentDomainFacadeCountDataTest extends ParameterTransactionFunctionalTestCase
{
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

    protected ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    abstract public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface;

    public function testCategory(): void
    {
        foreach ($this->categoryTestCasesProvider() as $testCaseName => $dataProvider) {
            /** @var \App\Model\Category\Category $category */
            $category = $dataProvider[0];
            /** @var \App\Model\Product\Filter\ProductFilterData $filterData */
            $filterData = $dataProvider[1];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData */
            $expectedCountData = $dataProvider[2];

            $filterConfig = $this->productFilterConfigFactory->createForCategory(
                $this->domain->getId(),
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
     * @return mixed[]
     */
    private function categoryNoFilterTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $countData = new ProductFilterCountData();

        $countData->countInStock = 7;
        $countData->countByBrandId = [
            2 => 4,
            14 => 1,
        ];
        $countData->countByFlagId = [
            3 => 2,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 5,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 3,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('A3') => 5,
                $this->getParameterValueIdForFirstDomain('A4') => 2,
            ],
            32 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 5,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 7,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 6,
            ],
            34 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 5,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            65 => [
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
     * @return mixed[]
     */
    private function categoryOneFlagTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);

        $countData = new ProductFilterCountData();

        $countData->countInStock = 2;
        $countData->countByBrandId = [
            2 => 1,
        ];
        $countData->countByFlagId = [
        ];
        $countData->countByParameterIdAndValueId = [
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 1,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 1,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('A3') => 2,
            ],
            32 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 2,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 1,
            ],
            34 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 1,
                $this->getParameterValueIdForFirstDomain('No') => 1,
            ],
            65 => [
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
     * @return mixed[]
     */
    private function categoryPriceTestCase(): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

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
            2 => 3,
            14 => 1,
        ];
        $countData->countByFlagId = [
            3 => 2,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 4,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,

            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('A3') => 5,
                $this->getParameterValueIdForFirstDomain('A4') => 1,
            ],
            32 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 4,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 6,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 5,
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
            ],
            34 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            65 => [
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
     * @return mixed[]
     */
    private function categoryFlagBrandAndParametersTestCase(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
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
            [[$firstDomainLocale => t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );

        $countData = new ProductFilterCountData();

        $countData->countInStock = 0;
        $countData->countByBrandId = [];
        $countData->countByFlagId = [];
        $countData->countByParameterIdAndValueId = [
            28 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
            ],
        ];

        return [
            $category,
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function categoryParametersTestCase(): array
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
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
            [[$firstDomainLocale => t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );

        $countData = new ProductFilterCountData();

        $countData->countInStock = 4;
        $countData->countByBrandId = [
            2 => 3,
            14 => 1,
        ];
        $countData->countByFlagId = [

        ];
        $countData->countByParameterIdAndValueId = [
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 4,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('A3') => 2,
                $this->getParameterValueIdForFirstDomain('A4') => 2,
            ],
            32 => [
                $this->getParameterValueIdForFirstDomain('2400x600') => 4,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 4,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 4,
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
            ],
            34 => [
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
     * @param mixed[] $namesByLocale
     * @param mixed[] $valuesTextsByLocales
     * @return \App\Model\Product\Filter\ParameterFilterData
     */
    private function createParameterFilterData(array $namesByLocale, array $valuesTextsByLocales): \App\Model\Product\Filter\ParameterFilterData
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
     * @return \App\Model\Product\Parameter\ParameterValue[]
     */
    private function getParameterValuesByLocalesAndTexts(array $valuesTextsByLocales): array
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
     * @return mixed[]
     */
    private function searchNoFilterTestCase(): array
    {
        $filterData = new ProductFilterData();
        $countData = new ProductFilterCountData();
        $countData->countInStock = 5;
        $countData->countByBrandId = [
            2 => 4,
            14 => 1,
        ];
        $countData->countByFlagId = [
            2 => 1,
            3 => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function searchOneFlagTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $countData = new ProductFilterCountData();
        $countData->countInStock = 1;
        $countData->countByBrandId = [
            2 => 1,
        ];
        $countData->countByFlagId = [];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function searchOneBrandTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $countData = new ProductFilterCountData();

        $countData->countInStock = 4;
        $countData->countByBrandId = [
            14 => 1,
        ];
        $countData->countByFlagId = [
            2 => 1,
            3 => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function searchPriceTestCase(): array
    {
        $filterData = new ProductFilterData();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
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
            2 => 2,
        ];
        $countData->countByFlagId = [];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function searchStockTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $countData = new ProductFilterCountData();
        $countData->countInStock = 5;
        $countData->countByBrandId = [
            2 => 4,
            14 => 1,
        ];
        $countData->countByFlagId = [
            2 => 1,
            3 => 1,
        ];

        return [
            'print',
            $filterData,
            $countData,
        ];
    }

    /**
     * @return mixed[]
     */
    private function searchPriceStockFlagBrandsTestCase(): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DELONGHI);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DEFENDER);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_GENIUS);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_HP);
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(20000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );

        $countData = new ProductFilterCountData();
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            2 => 1,
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
