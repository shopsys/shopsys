<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product\Connection;

use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory as FrontendApiProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader;
use Shopsys\LuigisBoxBundle\Model\Product\Filter\LuigisBoxFacetsToProductFilterOptionsMapper;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class ProductConnectionFactory
{
    public function __construct(
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
        protected readonly FrontendApiProductConnectionFactory $frontendApiProductConnectionFactory,
        protected readonly LuigisBoxFacetsToProductFilterOptionsMapper $luigisBoxFacetsToProductFilterConfigMapper,
    ) {
    }

    public function createConnectionPromiseForSearch(
        Closure $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData,
        ?string $orderingMode,
    ): Promise {
        $productFilterOptionsClosure = function () use ($productFilterData) {
            return $this->luigisBoxFacetsToProductFilterConfigMapper->map(LuigisBoxBatchLoader::getFacets(), $productFilterData);
        };
        $orderingMode = $orderingMode ?? $this->productOrderingModeProvider->getDefaultOrderingModeForSearch();

        return $this->getConnectionPromise(
            $retrieveProductClosure,
            $productFilterOptionsClosure,
            $argument,
            $orderingMode,
            $this->productOrderingModeProvider->getDefaultOrderingModeForSearch(),
        );
    }

    protected function getConnectionPromise(
        callable $retrieveClosure,
        Closure $productFilterOptionsClosure,
        Argument $argument,
        string $orderingMode,
        string $defaultOrderingMode,
    ): Promise {
        $paginator = $this->createPaginator($retrieveClosure, $productFilterOptionsClosure, $orderingMode, $defaultOrderingMode);

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, 0);

        $promise->then(function (ProductConnection $productConnection): void {
            $productConnection->setTotalCount(LuigisBoxBatchLoader::getTotalByType(TypeInLuigisBoxEnum::PRODUCT));
        });

        return $promise;
    }

    protected function createPaginator(
        callable $retrieveProductClosure,
        Closure $productFilterOptionsClosure,
        string $orderingMode,
        string $defaultOrderingMode,
    ): Paginator {
        return new Paginator(
            $retrieveProductClosure,
            Paginator::MODE_PROMISE,
            new ConnectionBuilder(null, function ($edges, $pageInfo) use ($productFilterOptionsClosure, $orderingMode, $defaultOrderingMode) {
                return $this->frontendApiProductConnectionFactory->createConnectionWithoutPaginator(
                    $edges,
                    $pageInfo,
                    $productFilterOptionsClosure,
                    $orderingMode,
                    null,
                    $defaultOrderingMode,
                );
            }),
        );
    }
}
