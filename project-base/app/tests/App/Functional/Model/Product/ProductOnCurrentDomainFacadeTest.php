<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Filter\ParameterFilterData;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Tests\App\Test\TransactionFunctionalTestCase;

abstract class ProductOnCurrentDomainFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ParameterRepository $parameterRepository;

    /**
     * @inject
     */
    protected PriceConverter $priceConverter;

    public function testFilterByMinimalPrice(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->minimalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(1000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(22, $paginationResult->getResults());
    }

    public function testFilterByMaximalPrice(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->maximalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(10000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(22, $paginationResult->getResults());
    }

    public function testFilterByStockAvailability(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PHONES);

        $productFilterData = new ProductFilterData();
        $productFilterData->inStock = true;
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(3, $paginationResult->getResults());
    }

    public function testFilterByFlag(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \App\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);
        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flagTopProduct];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
    }

    public function testFilterByFlagsReturnsProductsWithAnyOfUsedFlags(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        /** @var \App\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION);
        /** @var \App\Model\Product\Flag\Flag $flagActionProduct */
        $flagActionProduct = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flagTopProduct, $flagActionProduct];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(5, $paginationResult->getResults());
    }

    public function testFilterByBrand(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData = new ProductFilterData();
        $productFilterData->brands = [$brandCanon];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(4, $paginationResult->getResults());
    }

    public function testFilterByBrandsReturnsProductsWithAnyOfUsedBrands(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        /** @var \App\Model\Product\Brand\Brand $brandHp */
        $brandHp = $this->getReference(BrandDataFixture::BRAND_HP);
        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData = new ProductFilterData();
        $productFilterData->brands = [$brandCanon, $brandHp];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(5, $paginationResult->getResults());
    }

    public function testFilterByParameter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );

        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData];

        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
    }

    public function testFilterByParametersUsesOrWithinTheSameParameter(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [
                [$firstDomainLocale => t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                [$firstDomainLocale => t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ],
        );
        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(7, $paginationResult->getResults());
    }

    public function testFilterByParametersWithEmptyValue(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData1 = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [
                [$firstDomainLocale => t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                [$firstDomainLocale => t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ],
        );
        $parameterFilterData2 = $this->createParameterFilterData(
            [$firstDomainLocale => t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [],
        );

        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData1, $parameterFilterData2];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(7, $paginationResult->getResults());
    }

    public function testFilterByParametersUsesAndWithinDistinctParameters(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);

        $firstDomainLocale = $this->getFirstDomainLocale();
        $parameterFilterData1 = $this->createParameterFilterData(
            [$firstDomainLocale => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );
        $parameterFilterData2 = $this->createParameterFilterData(
            [$firstDomainLocale => t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [[$firstDomainLocale => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
        );
        $productFilterData = new ProductFilterData();
        $productFilterData->parameters = [$parameterFilterData1, $parameterFilterData2];
        $paginationResult = $this->getPaginationResultInCategory($productFilterData, $category);

        $this->assertCount(2, $paginationResult->getResults());
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

    public function testPagination(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        $productFilterData = new ProductFilterData();
        $productFilterData->minimalPrice = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(1000),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID,
        );

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 1, 10);
        $this->assertCount(10, $paginationResult->getResults(), ' page 1 limit 10');
        $this->assertSame(22, $paginationResult->getTotalCount());

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 2, 10);
        $this->assertCount(10, $paginationResult->getResults(), ' page 2 limit 10');
        $this->assertSame(22, $paginationResult->getTotalCount());

        $paginationResult = $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 3, 10);
        $this->assertCount(2, $paginationResult->getResults(), ' page 3 limit 10');
        $this->assertSame(22, $paginationResult->getTotalCount());
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInCategory(
        ProductFilterData $productFilterData,
        Category $category,
    ): PaginationResult {
        return $this->getPaginationResultInCategoryWithPageAndLimit($productFilterData, $category, 1, 1000);
    }

    public function testGetProductsForBrand(): void
    {
        $brand = $this->getReference(BrandDataFixture::BRAND_CANON);

        $paginationResult = $this->getPaginatedProductsForBrand($brand);

        $this->assertCount(8, $paginationResult->getResults());
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
            $brand->getId(),
        );
    }

    public function testGetPaginatedProductsForSearchWithFlagsAndBrand(): void
    {
        $productFilterData = new ProductFilterData();

        /** @var \App\Model\Product\Flag\Flag $flagTopProduct */
        $flagTopProduct = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $productFilterData->flags = [$flagTopProduct];

        /** @var \App\Model\Product\Brand\Brand $brandCanon */
        $brandCanon = $this->getReference(BrandDataFixture::BRAND_CANON);
        $productFilterData->brands = [$brandCanon];

        $paginationResult = $this->getPaginationResultInSearch($productFilterData, 'mg3550');

        $this->assertCount(1, $paginationResult->getResults());
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInSearch(
        ProductFilterData $productFilterData,
        string $searchText,
    ): PaginationResult {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();
        $page = 1;
        $limit = 1000;

        return $productOnCurrentDomainFacade->getPaginatedProductsForSearch(
            $searchText,
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
        );
    }

    public function testGetSearchAutocompleteProducts(): void
    {
        $paginationResult = $this->getSearchAutocompleteProducts('mg3550');

        $this->assertCount(1, $paginationResult->getResults());
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category $category
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInCategoryWithPageAndLimit(
        ProductFilterData $productFilterData,
        Category $category,
        int $page,
        int $limit,
    ): PaginationResult {
        $productOnCurrentDomainFacade = $this->getProductOnCurrentDomainFacade();

        return $productOnCurrentDomainFacade->getPaginatedProductsInCategory(
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $category->getId(),
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
            $limit,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    abstract public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface;
}
