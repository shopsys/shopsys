<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\ShopBundle\DataFixtures\Demo\BrandDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\FlagDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

abstract class ProductOnCurrentDomainFacadeCountDataTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory
     */
    protected $productFilterConfigFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    protected function setUp()
    {
        parent::setUp();
        $this->productFilterConfigFactory = $this->getContainer()->get(ProductFilterConfigFactory::class);
        $this->domain = $this->getContainer()->get(Domain::class);
        $this->productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    abstract public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface;

    /**
     * @param \Shopsys\ShopBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData
     * @dataProvider categoryTestCasesProvider
     */
    public function testCategory(Category $category, ProductFilterData $filterData, ProductFilterCountData $expectedCountData): void
    {
        $filterConfig = $this->productFilterConfigFactory->createForCategory($this->domain->getId(), $this->domain->getLocale(), $category);
        $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataInCategory($category->getId(), $filterConfig, $filterData);

        $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData));
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
            'stock' => $this->categoryStockTestCase(),
            'flag-brand-parameters' => $this->categoryFlagBrandAndParametersTestCase(),
            'parameters' => $this->categoryParametersTestCase(),
        ];
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData
     * @dataProvider searchTestCasesProvider
     */
    public function testSearch(string $searchText, ProductFilterData $filterData, ProductFilterCountData $expectedCountData): void
    {
        $filterConfig = $this->productFilterConfigFactory->createForSearch($this->domain->getId(), $this->domain->getLocale(), $searchText);
        $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch($searchText, $filterConfig, $filterData);

        $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData));
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

        $countData->countInStock = 10;
        $countData->countByBrandId = [
            2 => 6,
            14 => 2,
        ];
        $countData->countByFlagId = [
            1 => 5,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 10,
            ],
            11 => [
                57 => 8,
                123 => 2,
            ],
            30 => [
                7 => 5,
                11 => 5,
            ],
            29 => [
                53 => 7,
                188 => 3,
            ],
            31 => [
                55 => 3,
                97 => 7,
            ],
            28 => [
                51 => 10,
            ],
            4 => [
                7 => 10,
            ],
            10 => [
                59 => 1,
                61 => 9,
            ],
            33 => [
                7 => 8,
                11 => 2,
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
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_TOP_PRODUCT);

        $countData = new ProductFilterCountData();

        $countData->countInStock = 2;
        $countData->countByBrandId = [
            2 => 2,
        ];
        $countData->countByFlagId = [
            1 => 3,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 2,
            ],
            11 => [
                57 => 2,
            ],
            30 => [
                7 => 1,
                11 => 1,
            ],
            29 => [
                53 => 1,
                188 => 1,
            ],
            31 => [
                55 => 1,
                97 => 1,
            ],
            28 => [
                51 => 2,
            ],
            4 => [
                7 => 2,
            ],
            10 => [
                59 => 1,
                61 => 1,
            ],
            33 => [
                7 => 2,
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
        $countData->countInStock = 6;
        $countData->countByFlagId = [
            1 => 3,
            2 => 2,
        ];
        $countData->countByBrandId = [
            14 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 6,
            ],
            11 => [
                57 => 6,
            ],
            30 => [
                7 => 3,
                11 => 3,
            ],
            29 => [
                53 => 3,
                188 => 3,
            ],
            31 => [
                55 => 2,
                97 => 4,
            ],
            28 => [
                51 => 6,
            ],
            4 => [
                7 => 6,
            ],
            10 => [
                59 => 1,
                61 => 5,
            ],
            33 => [
                7 => 6,
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
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_TOP_PRODUCT);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_NEW_PRODUCT);

        $countData = new ProductFilterCountData();
        $countData->countInStock = 4;
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 4,
            ],
            11 => [
                57 => 4,
            ],
            30 => [
                7 => 2,
                11 => 2,
            ],
            29 => [
                53 => 3,
                188 => 1,
            ],
            31 => [
                55 => 2,
                97 => 2,
            ],
            28 => [
                51 => 4,
            ],
            4 => [
                7 => 4,
            ],
            10 => [
                59 => 1,
                61 => 3,
            ],
            33 => [
                7 => 4,
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
        $filterData->minimalPrice = Money::create(1000);
        $filterData->maximalPrice = Money::create(80000);

        $countData = new ProductFilterCountData();
        $countData->countInStock = 6;
        $countData->countByBrandId = [
            2 => 4,
            14 => 2,
        ];
        $countData->countByFlagId = [
            1 => 3,
            2 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 6,
            ],
            11 => [
                57 => 6,
            ],
            30 => [
                7 => 3,
                11 => 3,
            ],
            29 => [
                53 => 4,
                188 => 2,
            ],
            31 => [
                55 => 1,
                97 => 5,
            ],
            28 => [
                51 => 6,
            ],
            4 => [
                7 => 6,
            ],
            10 => [
                59 => 1,
                61 => 5,
            ],
            33 => [
                7 => 6,
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
    private function categoryStockTestCase(): array
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PHONES);
        $filterData = new ProductFilterData();
        $filterData->inStock = true;

        $countData = new ProductFilterCountData();
        $countData->countInStock = 2;
        $countData->countByBrandId = [
            3 => 1,
            20 => 1,
        ];
        $countData->countByFlagId = [
            1 => 2,
        ];
        $countData->countByParameterIdAndValueId = [
            17 => [
                7 => 1,
            ],
            11 => [
                23 => 1,
            ],
            19 => [
                11 => 1,
            ],
            12 => [
                11 => 1,
            ],
            18 => [
                11 => 1,
            ],
            14 => [
                27 => 1,
            ],
            16 => [
                31 => 1,
            ],
            15 => [
                29 => 1,
            ],
            13 => [
                25 => 1,
            ],
            10 => [
                21 => 1,
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
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        $firstDomainLocale = $domain->getDomainConfigById(1)->getLocale();
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        $filterData = new ProductFilterData();
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_CANON);
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_NEW_PRODUCT);
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
        $countData->countInStock = 2;
        $countData->countByBrandId = [
            14 => 1,
        ];
        $countData->countByFlagId = [];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 2,
            ],
            11 => [
                57 => 2,
            ],
            30 => [
                7 => 1,
                11 => 1,
            ],
            29 => [
                53 => 1,
                188 => 1,
            ],
            31 => [
                55 => 1,
                97 => 1,
            ],
            28 => [
                51 => 2,
            ],
            4 => [
                7 => 2,
            ],
            10 => [
                59 => 1,
                61 => 2,
            ],
            33 => [
                7 => 2,
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
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        $firstDomainLocale = $domain->getDomainConfigById(1)->getLocale();
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
        $countData->countInStock = 7;
        $countData->countByBrandId = [
            14 => 2,
            2 => 5,
        ];
        $countData->countByFlagId = [
            1 => 3,
            2 => 1,
        ];
        $countData->countByParameterIdAndValueId = [
            32 => [
                7 => 7,
            ],
            11 => [
                57 => 7,
                123 => 2,
            ],
            30 => [
                7 => 3,
                11 => 4,
            ],
            29 => [
                53 => 4,
                188 => 3,
            ],
            31 => [
                55 => 1,
                97 => 6,
            ],
            28 => [
                51 => 7,
            ],
            4 => [
                7 => 7,
            ],
            10 => [
                59 => 1,
                61 => 7,
            ],
            33 => [
                7 => 7,
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
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository */
        $parameterRepository = $this->getContainer()->get(ParameterRepository::class);

        $parameter = $parameterRepository->findParameterByNames($namesByLocale);
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
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $parameterValues = [];

        foreach ($valuesTextsByLocales as $valueTextsByLocales) {
            foreach ($valueTextsByLocales as $locale => $text) {
                /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue */
                $parameterValue = $em->getRepository(ParameterValue::class)->findOneBy([
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
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_NEW_PRODUCT);
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
            1 => 3,
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
        $filterData->minimalPrice = Money::create(5000);
        $filterData->maximalPrice = Money::create(50000);
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
        $filterData->flags[] = $this->getReference(FlagDataFixture::FLAG_NEW_PRODUCT);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DELONGHI);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_DEFENDER);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_GENIUS);
        $filterData->brands[] = $this->getReference(BrandDataFixture::BRAND_HP);
        $filterData->maximalPrice = Money::create(20000);

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
