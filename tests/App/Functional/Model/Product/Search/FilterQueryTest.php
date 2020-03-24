<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Search;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class FilterQueryTest extends ParameterTransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Elasticsearch\Client
     * @inject
     */
    private $elasticsearchClient;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
     * @inject
     */
    private $filterQueryFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     * @inject
     */
    private $priceConverter;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     * @inject
     */
    private $indexDefinitionLoader;

    public function testBrand(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter()
            ->filterByBrands([1]);

        $this->assertIdWithFilter($filter, [5]);
    }

    public function testFlag(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter()
            ->filterByFlags([3])
            ->applyDefaultOrdering();

        $this->assertIdWithFilter($filter, [1, 5, 50, 16, 33, 70, 39, 40, 45]);
    }

    public function testFlagBrand(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter()
            ->filterByBrands([12])
            ->filterByFlags([1])
            ->applyDefaultOrdering();

        $this->assertIdWithFilter($filter, [17, 19]);
    }

    public function testMultiFilter(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $filter = $this->createFilter()
            ->filterOnlyInStock()
            ->filterByCategory([9])
            ->filterByFlags([1])
            ->filterByPrices($pricingGroup, null, $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create(20), Domain::FIRST_DOMAIN_ID));

        $this->assertIdWithFilter($filter, [50]);
    }

    public function testParameters(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $parameters = [51 => [$this->getParameterValueIdForFirstDomain('hardcover'), $this->getParameterValueIdForFirstDomain('paper')], 50 => [$this->getParameterValueIdForFirstDomain('55'), $this->getParameterValueIdForFirstDomain('48')], 10 => [$this->getParameterValueIdForFirstDomain('50 g')]];

        $filter = $this->createFilter()
            ->filterByParameters($parameters);

        $this->assertIdWithFilter($filter, [25, 28]);
    }

    public function testOrdering(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $filter = $this->createFilter()
            ->filterByCategory([9])
            ->applyDefaultOrdering();

        $this->assertIdWithFilter($filter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40], 'top');

        $nameAscFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_ASC, $pricingGroup);
        $this->assertIdWithFilter($nameAscFilter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40], 'name asc');

        $nameDescFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_DESC, $pricingGroup);
        $this->assertIdWithFilter($nameDescFilter, [40, 39, 33, 50, 26, 28, 29, 27, 25, 72], 'name desc');

        $priceAscFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_ASC, $pricingGroup);
        $this->assertIdWithFilter($priceAscFilter, [40, 33, 50, 39, 29, 25, 26, 27, 28, 72], 'price asc');

        $priceDescFilter = $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_DESC, $pricingGroup);
        $this->assertIdWithFilter($priceDescFilter, [72, 28, 27, 26, 25, 29, 39, 50, 33, 40], 'price desc');
    }

    public function testMatchQuery(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter();

        $kittyFilter = $filter->search('kitty');
        $this->assertIdWithFilter($kittyFilter, [1, 102, 101]);

        $mg3550Filer = $filter->search('mg3550');
        $this->assertIdWithFilter($mg3550Filer, [9, 144, 10, 145]);
    }

    public function testPagination(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $filter = $this->createFilter()
            ->filterByCategory([9])
            ->applyDefaultOrdering();

        $this->assertIdWithFilter($filter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40]);

        $limit5Filter = $filter->setLimit(5);
        $this->assertIdWithFilter($limit5Filter, [72, 25, 27, 29, 28]);

        $limit1Filter = $filter->setLimit(1);
        $this->assertIdWithFilter($limit1Filter, [72]);

        $limit4Page2Filter = $filter->setLimit(4)
            ->setPage(2);
        $this->assertIdWithFilter($limit4Page2Filter, [28, 26, 50, 33]);

        $limit4Page3Filter = $filter->setLimit(4)
            ->setPage(3);
        $this->assertIdWithFilter($limit4Page3Filter, [39, 40]);

        $limit4Page4Filter = $filter->setLimit(4)
            ->setPage(4);
        $this->assertIdWithFilter($limit4Page4Filter, []);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param int[] $ids
     * @param string $message
     */
    protected function assertIdWithFilter(FilterQuery $filterQuery, array $ids, string $message = ''): void
    {
        $params = $filterQuery->getQuery();

        $params['_source'] = false;

        $result = $this->elasticsearchClient->search($params);
        $this->assertSame($ids, $this->extractIds($result), $message);
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
        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), Domain::FIRST_DOMAIN_ID);
        $filter = $this->filterQueryFactory->create($indexDefinition->getIndexAlias());

        return $filter->filterOnlySellable();
    }
}
