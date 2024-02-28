<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Product\Connection\ProductConnectionFactory;

class ProductSearchResultsProvider implements ProductSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Shopsys\LuigisBoxBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $client
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     * @param \Overblog\DataLoader\DataLoaderInterface $luigisBoxBatchLoader
     */
    public function __construct(
        protected readonly string $enabledDomainIds,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly LuigisBoxClient $client,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
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
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getProductsSearchResults(
        Argument $argument,
        ProductFilterData $productFilterData,
    ): Promise {
        $search = $argument['search'] ?? '';
        $orderingMode = $argument['orderingMode'];
        $endpoint = $argument['isAutocomplete'] === true ? LuigisBoxClient::ACTION_AUTOCOMPLETE : LuigisBoxClient::ACTION_SEARCH;
        $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->mapForSearch($productFilterData, $this->domain);

        return $this->productConnectionFactory->createConnectionPromiseForSearch(
            $search,
            function ($offset, $limit) use ($endpoint, $search, $luigisBoxFilter, $orderingMode) {
                return $this->luigisBoxBatchLoader->load(
                    new LuigisBoxBatchLoadData(
                        $search,
                        'product',
                        $endpoint,
                        $offset,
                        $limit,
                        $luigisBoxFilter,
                        $orderingMode,
                    ),
                );
            },
            $argument,
            $productFilterData,
            $orderingMode,
        );
    }
}
