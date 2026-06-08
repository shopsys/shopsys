<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Store\StoreFacade;
use Shopsys\FrontendApiBundle\Model\Store\StoreSearchTextCoordinatesProvider;
use Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions;

class StoresQuery extends AbstractQuery
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly StoreSearchTextCoordinatesProvider $storeSearchTextCoordinatesProvider,
    ) {
    }

    public function storesQuery(
        Argument $argument,
    ): Connection|Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $domainId = $this->domain->getId();

        /** @var string|null $searchText */
        $searchText = $argument->offsetGet('searchText');
        /** @var array{latitude: string, longitude: string}|null $coordinates */
        $coordinates = $argument->offsetGet('coordinates');

        $searchCoordinates = $this->storeSearchTextCoordinatesProvider->getCoordinatesFromSearchText($searchText);

        if ($searchCoordinates) {
            $coordinates = $searchCoordinates;
        }

        $filterOptions = new StoresFilterOptions(
            searchText: $searchText,
            coordinates: $coordinates,
        );

        $paginator = new Paginator(function ($offset, $limit) use ($domainId, $filterOptions) {
            return $this->storeFacade->getFilteredStores($domainId, $filterOptions, $limit, $offset);
        });

        $storesCount = $this->storeFacade->getFilteredStoresCount($domainId, $filterOptions);

        return $paginator->auto($argument, $storesCount);
    }

    public function storesByTransportQuery(
        Transport $transport,
        Argument $argument,
    ): Connection|Promise|null {
        if ($transport->isPersonalPickup()) {
            return $this->storesQuery($argument);
        }

        return null;
    }
}
