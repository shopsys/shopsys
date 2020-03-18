<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

abstract class ProductOnCurrentDomainFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     * @inject
     */
    private $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     * @inject
     */
    protected $priceConverter;

    public function testFilterByMinimalPrice()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->minimalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(1000), Domain::FIRST_DOMAIN_ID);
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(22, $paginationResult->getResults());
    }

    public function testFilterByMaximalPrice()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->maximalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(10000), Domain::FIRST_DOMAIN_ID);
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(22, $paginationResult->getResults());
    }

    public function testFilterByStockAvailability()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PHONES);

        $productFilterData = new ProductFilterData();
        $productFilterData->inStock = true;
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
    }

    public function testFilterByFlag()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_TOP_PRODUCT);
        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flagTopProduct];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
    }

    public function testFilterByFlagsReturnsProductsWithAnyOfUsedFlags()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_TOP_PRODUCT);
        /** @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flagActionProduct */
        $flagActionProduct = $this->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT);
        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flagTopProduct, $flagActionProduct];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(5, $paginationResult->getResults());
    }

    public function testFilterByBrand()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData = new ProductFilterData();
        $productFilterData->brands = [$brandCanon];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(6, $paginationResult->getResults());
    }

    public function testFilterByBrandsReturnsProductsWithAnyOfUsedBrands()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \App\Model\Product\Brand\Brand $brandHp */
        $brandHp = $this->getReference(BrandDataFixture::BRAND_HP);
        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData = new ProductFilterData();
        $productFilterData->brands = [$brandCanon, $brandHp];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(8, $paginationResult->getResults());
    }

    public function testFilterByParameter()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('4800x1200', [], 'dataFixtures', $firstDomainLocale)]]
        );

        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData];

        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(3, $paginationResult->getResults());
    }

    public function testFilterByParametersUsesOrWithinTheSameParameter()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [
                [$firstDomainLocale => t('4800x1200', [], 'dataFixtures', $firstDomainLocale)],
                [$firstDomainLocale => t('2400x600', [], 'dataFixtures', $firstDomainLocale)],
            ]
        );
        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(10, $paginationResult->getResults());
    }

    public function testFilterByParametersWithEmptyValue(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData1 = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [
                [$firstDomainLocale => t('4800x1200', [], 'dataFixtures', $firstDomainLocale)],
                [$firstDomainLocale => t('2400x600', [], 'dataFixtures', $firstDomainLocale)],
            ]
        );
        $parameterFilterData2 = $this->createParameterFilterData(
            [$firstDomainLocale => t('LCD', [], 'dataFixtures', $firstDomainLocale)],
            []
        );

        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData1, $parameterFilterData2];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(10, $paginationResult->getResults());
    }

    public function testFilterByParametersUsesAndWithinDistinctParameters()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData1 = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $parameterFilterData2 = $this->createParameterFilterData(
            [$firstDomainLocale => t('LCD', [], 'dataFixtures', $firstDomainLocale)],
            [[$firstDomainLocale => t('Yes', [], 'dataFixtures', $firstDomainLocale)]]
        );
        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData1, $parameterFilterData2];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
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

    public function testPagination(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->minimalPrice = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(1000), Domain::FIRST_DOMAIN_ID);

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 1, 10);
        $this->assertCount(10, $paginationResult->getResults());
        $this->assertSame(22, $paginationResult->getTotalCount());

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 2, 10);
        $this->assertCount(10, $paginationResult->getResults());
        $this->assertSame(22, $paginationResult->getTotalCount());

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 3, 2);
        $this->assertCount(2, $paginationResult->getResults());
        $this->assertSame(22, $paginationResult->getTotalCount());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInCategory(ProductFilterData $productFilterData, Category $category): PaginationResult
    {
        return $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 1, 1000);
    }

    public function testGetProductsForBrand(): void
    {
        $brand = $this->getReference(BrandDataFixture::BRAND_CANON);

        $paginationResult = $this->getPaginatedProductsForBrand($brand);

        $this->assertCount(10, $paginationResult->getResults());
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedProductsForBrand(Brand $brand): PaginationResult
    {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
        $page = 1;
        $limit = 1000;

        return $productOnCurrentDomainFacade->getPaginatedProductsForBrand(
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $brand->getId()
        );
    }

    public function testGetPaginatedProductsForSearchWithFlagsAndBrand(): void
    {
        $productFilterData = new ProductFilterData();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_NEW_PRODUCT);
        $productFilterData->flags = [$flagTopProduct];

        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData->brands = [$brandCanon];

        $paginationResult = $this->getPaginationResultInSearch($productFilterData, 'mg3550');

        $this->assertCount(3, $paginationResult->getResults());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInSearch(ProductFilterData $productFilterData, string $searchText): PaginationResult
    {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
        $page = 1;
        $limit = 1000;

        return $productOnCurrentDomainFacade->getPaginatedProductsForSearch(
            $searchText,
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit
        );
    }

    public function testGetSearchAutocompleteProducts(): void
    {
        $paginationResult = $this->getSearchAutocompleteProducts('mg3550');

        $this->assertCount(4, $paginationResult->getResults());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category $category
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInCategoryWithPageAndLimit(ProductFilterData $productFilterData, Category $category, int $page, int $limit): PaginationResult
    {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();

        return $productOnCurrentDomainFacade->getPaginatedProductsInCategory(
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $category->getId()
        );
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteProducts(string $searchText): PaginationResult
    {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
        $limit = 1000;

        return $productOnCurrentDomainFacade->getSearchAutocompleteProducts(
            $searchText,
            $limit
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    abstract public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface;
}
