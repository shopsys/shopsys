<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Product\Connection\Exception\LuigisBoxPaginationBackwardsNotSupportedException;

class ProductSearchResultsProvider implements ProductSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $client
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     */
    public function __construct(
        protected readonly string $enabledDomainIds,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly LuigisBoxClient $client,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
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
            throw new LuigisBoxPaginationBackwardsNotSupportedException();
        }

        $search = $argument['search'] ?? '';
        $limit = $argument['first'] ?? 0;
        $after = $argument['after'] ?? null;
        $orderingMode = $argument['orderingMode'] === null || $argument['orderingMode'] === ProductListOrderingConfig::ORDER_BY_RELEVANCE ? null : $argument['orderingMode'];

        $connectionBuilder = new ConnectionBuilder();
        $offset = $connectionBuilder->getOffsetWithDefault($after, -1) + 1;
        $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->mapForSearch(LuigisBoxClient::LUIGIS_BOX_TYPE_PRODUCT, $productFilterData, $this->domain);

        $result = $this->client->getData(
            $search,
            LuigisBoxClient::LUIGIS_BOX_TYPE_PRODUCT,
            $argument['isAutocomplete'] === true ? LuigisBoxClient::LUIGIS_BOX_ENDPOINT_AUTOCOMPLETE : LuigisBoxClient::LUIGIS_BOX_ENDPOINT_SEARCH,
            $offset,
            $limit,
            $luigisBoxFilter,
            $orderingMode,
        );

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
}
