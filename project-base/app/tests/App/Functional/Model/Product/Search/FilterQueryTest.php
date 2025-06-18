<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Search;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use DateTimeImmutable;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;

class FilterQueryTest extends ParameterTransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private Client $elasticsearchClient;

    /**
     * @inject
     */
    private FilterQueryFactory $filterQueryFactory;

    /**
     * @inject
     */
    private PriceConverter $priceConverter;

    /**
     * @inject
     */
    private IndexDefinitionLoader $indexDefinitionLoader;

    public function testBrand(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $brandApple = $this->getReference(BrandDataFixture::BRAND_APPLE, Brand::class);

        $filter = $this->createFilter()
            ->filterByBrands([$brandApple->getId()]);

        $this->assertIdsWithFilter($filter, [5]);
    }

    public function testFlag(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $flagNew = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);

        $filter = $this->createFilter()
            ->filterByFlags([$flagNew->getId()])
            ->applyOrderingByIdAscending();

        $this->assertIdsWithFilter($filter, [9, 10, 13, 14, 19, 21, 22, 25, 27, 29, 31, 35, 42, 44, 50, 144]);
    }

    public function testFlagBrand(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $brandGenius = $this->getReference(BrandDataFixture::BRAND_GENIUS, Brand::class);
        $flagSale = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE, Flag::class);

        $filter = $this->createFilter()
            ->filterByBrands([$brandGenius->getId()])
            ->filterByFlags([$flagSale->getId()])
            ->applyOrderingByIdAscending();

        $this->assertIdsWithFilter($filter, []);
    }

    public function testMultiFilter(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class);
        $flagSale = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE, Flag::class);

        $filter = $this->createFilter()
            ->filterOnlyInStock()
            ->filterByCategory($categoryBooks->getId())
            ->filterByFlags([$flagSale->getId()])
            ->filterByPrices(
                $pricingGroup,
                null,
                $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
                    Money::create(20),
                    $currencyCzk,
                    Domain::FIRST_DOMAIN_ID,
                ),
            );

        $this->assertIdsWithFilter($filter, [33]);
    }

    public function testFilterByPriceProductWithSpecialPrice(): void
    {
        $testedProductId = 28; // the product has active special price 61 CZK with original price 65 CZK
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );
        $categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class);
        $filter = $this->createFilter()
            ->filterByCategory($categoryBooks->getId())
            ->filterByPrices(
                $pricingGroup,
                $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
                    Money::create(64),
                    $currencyCzk,
                    Domain::FIRST_DOMAIN_ID,
                ),
            )
            ->applyOrderingByIdAscending();
        $this->assertIdNotInResult($filter, $testedProductId, 'product with special price lower than filter price from should not be in result');

        $filter = $this->createFilter()
            ->filterByCategory($categoryBooks->getId())
            ->filterByPrices(
                $pricingGroup,
                $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
                    Money::create(60),
                    $currencyCzk,
                    Domain::FIRST_DOMAIN_ID,
                ),
            )
            ->applyOrderingByIdAscending();
        $this->assertIdInResult($filter, $testedProductId, 'product with special price higher than filter price from should be in result');
    }

    public function testParameters(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $parameterCover = $this->getReference(ParameterDataFixture::PARAM_COVER, Parameter::class);
        $parameterPagesCount = $this->getReference(ParameterDataFixture::PARAM_PAGES_COUNT, Parameter::class);
        $parameterDimensions = $this->getReference(ParameterDataFixture::PARAM_DIMENSIONS, Parameter::class);

        $parameters = [
            $parameterCover->getId() => [
                $this->getParameterValueIdForFirstDomain(
                    'hardcover',
                ),
                $this->getParameterValueIdForFirstDomain(
                    'paper',
                ),
            ],
            $parameterPagesCount->getId() => [
                $this->getParameterValueIdForFirstDomain(
                    '55',
                    true,
                ),
                $this->getParameterValueIdForFirstDomain(
                    '48',
                    true,
                ),
            ],
            $parameterDimensions->getId() => [
                $this->getParameterValueIdForFirstDomain(
                    '50',
                    true,
                ),
            ],
        ];

        $filter = $this->createFilter()
            ->filterByParameters($parameters);

        $this->assertIdsWithFilter($filter, []);
    }

    public function testOrdering(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class);

        $filter = $this->createFilter()
            ->filterByCategory($categoryBooks->getId())
            ->applyOrderingByIdAscending();

        $this->assertIdsWithFilter($filter, [25, 26, 27, 28, 29, 33, 39, 40, 50, 72], 'by id asc');

        $nameAscFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_ASC, $pricingGroup);
        $this->assertIdsWithFilter($nameAscFilter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40], 'name asc');

        $nameDescFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_DESC, $pricingGroup);
        $this->assertIdsWithFilter($nameDescFilter, [40, 39, 33, 50, 26, 28, 29, 27, 25, 72], 'name desc');

        $priceAscFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_ASC, $pricingGroup);
        $this->assertIdsWithFilter($priceAscFilter, [40, 33, 50, 39, 29, 25, 26, 27, 28, 72], 'price asc');

        $priceDescFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_DESC, $pricingGroup);
        $this->assertIdsWithFilter($priceDescFilter, [72, 28, 27, 25, 26, 29, 39, 50, 33, 40], 'price desc');

        $priorityFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRIORITY, $pricingGroup);
        $this->assertIdsWithFilter($priorityFilter, [72, 25, 27, 29, 28, 26, 33, 39, 40, 50], 'priority');
    }

    public function testMatchQuery(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter();

        $kittyFilter = $filter->search('kitty');
        $this->assertIdsWithFilter($kittyFilter, [1]);

        $mg3550Filer = $filter->search('mg3550');
        $this->assertIdsWithFilter($mg3550Filer, [9]);
    }

    public function testPagination(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class);

        $filter = $this->createFilter()
            ->filterByCategory($categoryBooks->getId())
            ->applyOrderingByIdAscending();

        $this->assertIdsWithFilter($filter, [25, 26, 27, 28, 29, 33, 39, 40, 50, 72]);

        $limit5Filter = $filter->setLimit(5);
        $this->assertIdsWithFilter($limit5Filter, [25, 26, 27, 28, 29]);

        $limit1Filter = $filter->setLimit(1);
        $this->assertIdsWithFilter($limit1Filter, [25]);

        $limit4Page2Filter = $filter->setLimit(4)
            ->setPage(2);
        $this->assertIdsWithFilter($limit4Page2Filter, [29, 33, 39, 40]);

        $limit4Page3Filter = $filter->setLimit(4)
            ->setPage(3);
        $this->assertIdsWithFilter($limit4Page3Filter, [50, 72]);

        $limit4Page4Filter = $filter->setLimit(4)
            ->setPage(4);
        $this->assertIdsWithFilter($limit4Page4Filter, []);
    }

    public function testFilterBySellingFrom(): void
    {
        $filter = $this->createFilter()
            ->filterBySellingFrom(new DateTimeImmutable('-30 days'))
            ->applyOrderingByIdAscending();
        $this->assertIdsWithFilter($filter, [44, 144]);
    }

    public function testFilterWithActiveSpecialPriceOnly(): void
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $filter = $this->createFilter()
            ->filterWithActiveSpecialPriceOnly($pricingGroup)
            ->applyOrderingByIdAscending();

        $this->assertIdsWithFilter($filter, [19, 27, 28, 54, 69, 117]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param int[] $ids
     * @param string $message
     */
    protected function assertIdsWithFilter(FilterQuery $filterQuery, array $ids, string $message = ''): void
    {
        $this->assertSame($ids, $this->getResultIds($filterQuery), $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param int $id
     * @param string $message
     */
    protected function assertIdNotInResult(FilterQuery $filterQuery, int $id, string $message = ''): void
    {
        $this->assertNotContains($id, $this->getResultIds($filterQuery), $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param int $id
     * @param string $message
     */
    protected function assertIdInResult(FilterQuery $filterQuery, int $id, string $message = ''): void
    {
        $this->assertContains($id, $this->getResultIds($filterQuery), $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @return int[]
     */
    protected function getResultIds(FilterQuery $filterQuery): array
    {
        $params = $filterQuery->getQuery();
        $params['_source'] = false;
        $result = $this->elasticsearchClient->search($params);

        return $this->extractIds($result);
    }

    /**
     * @param array $result
     * @return int[]
     */
    protected function extractIds(array $result): array
    {
        $hits = $result['hits']['hits'];

        return array_map(static function ($element) {
            return (int)$element['_id'];
        }, $hits);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    protected function createFilter(): FilterQuery
    {
        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(
            ProductIndex::getName(),
            Domain::FIRST_DOMAIN_ID,
        );
        $filter = $this->filterQueryFactory->create($indexDefinition->getIndexAlias());

        return $filter->filterOnlySellable();
    }
}
