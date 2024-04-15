<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;

class ProductSearchResultsProvider implements ProductSearchResultsProviderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     */
    public function __construct(
        protected readonly ProductFilterDataFactory $productFilterDataFactory,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
        protected readonly ProductFilterFacade $productFilterFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function getProductsSearchResults(
        Argument $argument,
    ): ProductConnection {
        $search = $argument['searchInput']['search'] ?? '';

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument,
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($search, $productFilterData) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    ProductListOrderingConfig::ORDER_BY_RELEVANCE,
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

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
