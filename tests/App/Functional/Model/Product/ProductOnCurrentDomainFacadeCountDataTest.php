<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

abstract class ProductOnCurrentDomainFacadeCountDataTest extends ParameterTransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory
     * @inject
     */
    protected $productFilterConfigFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     * @inject
     */
    protected $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     * @inject
     */
    protected $priceConverter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

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
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData */
            $filterData = $dataProvider[1];
            /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData */
            $expectedCountData = $dataProvider[2];

            $filterConfig = $this->productFilterConfigFactory->createForCategory($this->domain->getId(), $this->domain->getLocale(), $category);
            $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataInCategory($category->getId(), $filterConfig, $filterData);
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
            'one-brand' => $this->categoryOneBrandTestCase(),
            'all-flags-all-brands' => $this->categoryAllFlagsAllBrandsTestCase(),
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

            $filterConfig = $this->productFilterConfigFactory->createForSearch($this->domain->getId(), $this->domain->getLocale(), $searchText);
            $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch($searchText, $filterConfig, $filterData);

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
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $countData = new ProductFilterCountData();

        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            2 => 6,
            14 => 2,
        ];
        $countData->countByFlagId = [
            4 => 5,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 10,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 8,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 5,
                $this->getParameterValueIdForFirstDomain('No') => 5,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 7,
                $this->getParameterValueIdForFirstDomain('A4') => 3,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 3,
                $this->getParameterValueIdForFirstDomain('2400x600') => 7,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 10,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 10,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 9,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 8,
                $this->getParameterValueIdForFirstDomain('No') => 2,
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
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);

        $countData = new ProductFilterCountData();

        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            2 => 2,
        ];
        $countData->countByFlagId = [
            4 => 3,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 1,
                $this->getParameterValueIdForFirstDomain('No') => 1,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 1,
                $this->getParameterValueIdForFirstDomain('A4') => 1,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 1,
                $this->getParameterValueIdForFirstDomain('2400x600') => 1,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 2,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 1,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
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
    private function categoryOneBrandTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $countData = new ProductFilterCountData();
        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByFlagId = [
            4 => 3,
            2 => 2,
        ];
        $countData->countByBrandId = [
            14 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 6,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 3,
                $this->getParameterValueIdForFirstDomain('No') => 3,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 3,
                $this->getParameterValueIdForFirstDomain('A4') => 3,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 4,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 6,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 5,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
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
    private function categoryAllFlagsAllBrandsTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_HP);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);

        $countData = new ProductFilterCountData();
        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 4,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
                $this->getParameterValueIdForFirstDomain('No') => 2,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 3,
                $this->getParameterValueIdForFirstDomain('A4') => 1,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 2,
                $this->getParameterValueIdForFirstDomain('2400x600') => 2,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 4,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 4,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 3,
            ],
            33 => [
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
     * @return array
     */
    private function categoryPriceTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->minimalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create((1000 / 1.21) . ''), Domain::FIRST_DOMAIN_ID);
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create((80000 / 1.21) . ''), Domain::FIRST_DOMAIN_ID);

//        $filterData->minimalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(1000), Domain::FIRST_DOMAIN_ID);
//        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(80000), Domain::FIRST_DOMAIN_ID);

        $countData = new ProductFilterCountData();
        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            2 => 4,
            14 => 2,
        ];
        $countData->countByFlagId = [
            4 => 3,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 6,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 3,
                $this->getParameterValueIdForFirstDomain('No') => 3,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 4,
                $this->getParameterValueIdForFirstDomain('A4') => 2,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 1,
                $this->getParameterValueIdForFirstDomain('2400x600') => 5,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 6,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 5,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 6,
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
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Dimensions', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('449x304x152 mm', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], 'dataFixtures', $firstDomainLocale)], [$firstDomainLocale => t('4800x1200', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Weight', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('3.5 kg', [], 'dataFixtures', $firstDomainLocale)]]
        );

        $countData = new ProductFilterCountData();
        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            14 => 1,
        ];
        $countData->countByFlagId = [];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 1,
                $this->getParameterValueIdForFirstDomain('No') => 1,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 1,
                $this->getParameterValueIdForFirstDomain('A4') => 1,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 1,
                $this->getParameterValueIdForFirstDomain('2400x600') => 1,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 2,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 2,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 2,
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
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Dimensions', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('449x304x152 mm', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], 'dataFixtures', $firstDomainLocale)], [$firstDomainLocale => t('4800x1200', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $filterData->parameters[] = $this->createParameterFilterData(
            [$firstDomainLocale => t('Weight', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('3.5 kg', [], 'dataFixtures', $firstDomainLocale)]]
        );

        $countData = new ProductFilterCountData();
        /*
         * availability filter is temporary disabled util stocks will be full implemented,
         * than u have to setup new correct expected values
         */
        $countData->countInStock = 0;
        $countData->countByBrandId = [
            14 => 2,
            2 => 5,
        ];
        $countData->countByFlagId = [
            4 => 3,
            2 => 1,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            11 => [
                $this->getParameterValueIdForFirstDomain('449x304x152 mm') => 7,
                $this->getParameterValueIdForFirstDomain('426x306x145 mm') => 2,
            ],
            30 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 3,
                $this->getParameterValueIdForFirstDomain('No') => 4,
            ],
            29 => [
                $this->getParameterValueIdForFirstDomain('A3') => 4,
                $this->getParameterValueIdForFirstDomain('A4') => 3,
            ],
            31 => [
                $this->getParameterValueIdForFirstDomain('4800x1200') => 1,
                $this->getParameterValueIdForFirstDomain('2400x600') => 6,
            ],
            28 => [
                $this->getParameterValueIdForFirstDomain('inkjet') => 7,
            ],
            4 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
            ],
            10 => [
                $this->getParameterValueIdForFirstDomain('5.4 kg') => 1,
                $this->getParameterValueIdForFirstDomain('3.5 kg') => 7,
            ],
            33 => [
                $this->getParameterValueIdForFirstDomain('Yes') => 7,
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
     * @return \App\Model\Product\Filter\ParameterFilterData
     */
    private function createParameterFilterData(array $namesByLocale, array $valuesTextsByLocales)
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameter */
        $parameter = $this->parameterRepository->findParameterByNames($namesByLocale);
        /** @var \App\Model\Product\Parameter\ParameterValue[] $parameterValues */
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
                /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue */
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
        $countData->countInStock = 38;
        $countData->countByBrandId = [
            8 => 1,
            11 => 1,
            19 => 2,
            10 => 1,
            2 => 10,
            4 => 1,
            16 => 1,
            15 => 1,
            6 => 1,
            14 => 2,
            12 => 2,
            3 => 2,
            9 => 1,
        ];
        $countData->countByFlagId = [
            1 => 15,
            2 => 5,
            3 => 3,
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
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $countData = new ProductFilterCountData();
        $countData->countInStock = 11;
        $countData->countByBrandId = [
            2 => 3,
            3 => 1,
            10 => 1,
            11 => 1,
            12 => 1,
            14 => 1,
            15 => 1,
            16 => 1,
            19 => 2,
        ];
        $countData->countByFlagId = [
            2 => 2,
            3 => 2,
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
    private function searchOneBrandTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $countData = new ProductFilterCountData();

        $countData->countInStock = 10;
        $countData->countByBrandId = [
            3 => 2,
            4 => 1,
            6 => 1,
            8 => 1,
            10 => 1,
            11 => 1,
            12 => 2,
            14 => 2,
            15 => 1,
            16 => 1,
            19 => 2,
            9 => 1,
        ];
        $countData->countByFlagId = [
            4 => 3,
            2 => 2,
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
        $filterData->minimalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(5000), Domain::FIRST_DOMAIN_ID);
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(50000), Domain::FIRST_DOMAIN_ID);
        $countData = new ProductFilterCountData();
        $countData->countInStock = 9;
        $countData->countByBrandId = [
            2 => 4,
            3 => 1,
            4 => 1,
            11 => 1,
            15 => 1,
        ];
        $countData->countByFlagId = [
            1 => 2,
            2 => 2,
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
    private function searchStockTestCase(): array
    {
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $countData = new ProductFilterCountData();
        $countData->countInStock = 38;
        $countData->countByBrandId = [
            2 => 10,
            3 => 2,
            4 => 1,
            6 => 1,
            8 => 1,
            10 => 1,
            11 => 1,
            12 => 2,
            14 => 2,
            16 => 1,
        ];
        $countData->countByFlagId = [
            1 => 11,
            2 => 4,
            3 => 2,
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
        $filterData = new ProductFilterData();
        $filterData->inStock = true;
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DELONGHI);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DEFENDER);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_GENIUS);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_HP);
        $filterData->maximalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(20000), Domain::FIRST_DOMAIN_ID);

        $countData = new ProductFilterCountData();
        $countData->countInStock = 3;
        $countData->countByBrandId = [
            2 => 3,
            3 => 1,
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
            if (empty($values)) {
                unset($result->countByParameterIdAndValueId[$parameterId]);
            }
        }
        return $result;
    }
}
