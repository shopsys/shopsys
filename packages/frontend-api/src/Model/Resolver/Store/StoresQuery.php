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
use Shopsys\FrontendApiBundle\Model\Resolver\Store\Exception\TooManyStoreSearchAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Store\StoreConnection;
use Shopsys\FrontendApiBundle\Model\Store\StoreFacade;
use Shopsys\FrontendApiBundle\Model\Store\StoreSearchTextCoordinatesProvider;
use Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class StoresQuery extends AbstractQuery
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly StoreSearchTextCoordinatesProvider $storeSearchTextCoordinatesProvider,
        protected readonly RateLimiterFactoryInterface $storesSearchRateLimiter,
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function storesQuery(
        Argument $argument,
    ): StoreConnection|Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $domainId = $this->domain->getId();

        /** @var string|null $searchText */
        $searchText = $argument->offsetGet('searchText');
        /** @var array{latitude: string, longitude: string}|null $coordinates */
        $coordinates = $argument->offsetGet('coordinates');

        if ($this->hasSearchText($searchText)) {
            $this->checkStoresSearchRateLimit();
        }

        $searchCoordinates = $searchText !== null
            ? $this->storeFacade->findStoreCoordinatesBySearchText($domainId, $searchText)
            : null;

        $searchCoordinates ??= $this->storeSearchTextCoordinatesProvider->getCoordinatesFromSearchText($searchText);

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
        $connection = $paginator->auto($argument, $storesCount);

        return new StoreConnection(
            $connection->getEdges(),
            $connection->getPageInfo(),
            $searchCoordinates,
            $connection->getTotalCount(),
        );
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

    protected function hasSearchText(?string $searchText): bool
    {
        return $searchText !== null && trim($searchText) !== '';
    }

    protected function checkStoresSearchRateLimit(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $clientIp = $request?->getClientIp() ?? 'unknown';

        $limit = $this->storesSearchRateLimiter
            ->create('stores-search:' . $clientIp)
            ->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyStoreSearchAttemptsUserError('Too many store search attempts. Try again later.');
        }
    }
}
