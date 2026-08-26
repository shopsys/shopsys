<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadResult;
use Shopsys\LuigisBoxBundle\Model\Facet\FacetFactory;
use Shopsys\LuigisBoxBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class ProductSearchResultsProvider extends SearchResultsProvider implements ProductSearchResultsProviderInterface
{
    public function __construct(
        string $enabledDomainIds,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly Domain $domain,
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
        protected readonly ProductFilterDataMapper $productFilterDataMapper,
        protected readonly FacetFactory $facetFactory,
        protected readonly ParameterFacade $parameterFacade,
    ) {
        parent::__construct($enabledDomainIds);
    }

    #[Override]
    public function getProductsSearchResults(
        Argument $argument,
    ): Promise {
        $orderingMode = $argument['orderingMode'];
        $productFilterData = $this->productFilterDataMapper->mapFrontendApiFilterToProductFilterData($argument['filter'] ?? []);
        $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->map(TypeInLuigisBoxEnum::PRODUCT, $productFilterData, $this->domain);
        $parameterUuids = $argument['parameters'] ?? [];
        $facetNames = [];
        $batchLoadResult = null;

        foreach ($this->parameterFacade->getParametersByUuids($parameterUuids) as $parameter) {
            $facetNames[] = $parameter->getName();
        }

        $facetNames = array_unique([...$facetNames, ...$this->facetFactory->mapFacetsFromProductFilterData($productFilterData)], SORT_REGULAR);

        return $this->productConnectionFactory->createConnectionPromiseForSearch(
            function ($offset, $limit) use ($argument, $luigisBoxFilter, $facetNames, &$batchLoadResult) {
                $batchLoadData = $this->luigisBoxBatchLoadDataFactory->createForSearch(
                    TypeInLuigisBoxEnum::PRODUCT,
                    $limit,
                    $offset,
                    $argument,
                    $luigisBoxFilter,
                    $facetNames,
                );

                return $this->luigisBoxBatchLoader->load($batchLoadData)
                    ->then(static function (LuigisBoxBatchLoadResult $result) use (&$batchLoadResult): array {
                        $batchLoadResult = $result;

                        return $result->getData();
                    });
            },
            $argument,
            $productFilterData,
            $orderingMode,
            static function () use (&$batchLoadResult): ?LuigisBoxBatchLoadResult {
                return $batchLoadResult;
            },
        );
    }
}
