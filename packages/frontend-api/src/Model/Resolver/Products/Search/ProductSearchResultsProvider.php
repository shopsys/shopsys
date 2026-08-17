<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;

class ProductSearchResultsProvider implements ProductSearchResultsProviderInterface
{
    public function __construct(
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
        protected readonly ProductFilterFacade $productFilterFacade,
    ) {
    }

    #[Override]
    public function getProductsSearchResults(
        Argument $argument,
    ): ProductConnection {
        $search = $argument['searchInput']['search'] ?? '';

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument,
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($search, $productFilterData, $argument) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search,
                );
            },
            $this->productFacade->getFilteredProductsCountOnCurrentDomain($productFilterData, $search),
            $argument,
            $productFilterData,
            $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
        );
    }

    #[Override]
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
