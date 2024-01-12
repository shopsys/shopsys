<?php

declare(strict_types=1);

namespace Shopsys\PersooBundle\Model\Product;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface;
use Shopsys\PersooBundle\Component\Persoo\Filter\ProductFilterToPersooFilterMapper;
use Shopsys\PersooBundle\Component\Persoo\PersooClient;
use Shopsys\PersooBundle\Model\Product\Connection\Exception\PersooPaginationBackwardsNotSupportedException;

class ProductSearchResultsProvider implements ProductSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\PersooBundle\Component\Persoo\PersooClient $client
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\PersooBundle\Component\Persoo\Filter\ProductFilterToPersooFilterMapper $productFilterToPersooFilterMapper
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     */
    public function __construct(
        protected readonly string $enabledDomainIds,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly PersooClient $client,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductFilterToPersooFilterMapper $productFilterToPersooFilterMapper,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
    ) {
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool
    {
        $enabledDomainIds = array_map(static fn (string $domainId) => (int)$domainId, explode(',', $this->enabledDomainIds));

        return in_array($domainId, $enabledDomainIds, true);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function getProductsSearchResults(
        Argument $argument,
        ProductFilterData $productFilterData,
    ): ProductConnection {
        if (array_key_exists('last', $argument->getArrayCopy())) {
            throw new PersooPaginationBackwardsNotSupportedException();
        }

        $search = $argument['search'] ?? '';
        $limit = $argument['first'] ?? 0;
        $after = $argument['after'] ?? null;

        $connectionBuilder = new ConnectionBuilder();
        $offset = $connectionBuilder->getOffsetWithDefault($after, -1) + 1;
        $page = $this->getPageFromOffsetAndLimit($offset, $limit);
        $persooFilter = $this->productFilterToPersooFilterMapper->mapForSearch($productFilterData, $this->domain);
        $result = $this->client->getData($search, PersooClient::PERSOO_INDEX_PRODUCTS, PersooClient::PERSOO_ACTION_SEARCH, $page, $limit, $persooFilter);

        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($result->getIds(), $limit);
        $sortedProducts = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery)->getHits();

        return $this->productConnectionFactory->createConnectionForSearchFromArray(
            $sortedProducts,
            $search,
            $offset,
            $limit,
            $result->getItemsCount(),
            $productFilterData,
            $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
        );
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return int
     */
    protected function getPageFromOffsetAndLimit(int $offset, int $limit): int
    {
        return (int)ceil($offset / $limit);
    }
}
