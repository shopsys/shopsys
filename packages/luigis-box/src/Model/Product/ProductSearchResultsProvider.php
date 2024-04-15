<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class ProductSearchResultsProvider extends SearchResultsProvider implements ProductSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Shopsys\LuigisBoxBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $client
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     * @param \Overblog\DataLoader\DataLoaderInterface $luigisBoxBatchLoader
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
     */
    public function __construct(
        string $enabledDomainIds,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly LuigisBoxClient $client,
        protected readonly Domain $domain,
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
        protected readonly ProductFilterDataMapper $productFilterDataMapper,
    ) {
        parent::__construct($enabledDomainIds);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getProductsSearchResults(
        Argument $argument,
    ): Promise {
        $orderingMode = $argument['orderingMode'];
        $productFilterData = $this->productFilterDataMapper->mapFrontendApiFilterToProductFilterData($argument['filter'] ?? []);
        $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->map(TypeInLuigisBoxEnum::PRODUCT, $productFilterData, $this->domain);

        return $this->productConnectionFactory->createConnectionPromiseForSearch(
            function ($offset, $limit) use ($argument, $luigisBoxFilter) {
                return $this->luigisBoxBatchLoader->load(
                    $this->luigisBoxBatchLoadDataFactory->createForSearch(
                        TypeInLuigisBoxEnum::PRODUCT,
                        $limit,
                        $offset,
                        $argument,
                        $luigisBoxFilter,
                    ),
                );
            },
            $argument,
            $productFilterData,
            $orderingMode,
        );
    }
}
