<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\Store\Exception\TooManyStoreSearchAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Transport\ProductDeliveryStore;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class StoreConnectionFactory
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly StoreSearchTextCoordinatesProvider $storeSearchTextCoordinatesProvider,
        protected readonly RateLimiterFactoryInterface $storesSearchRateLimiter,
        protected readonly RequestStack $requestStack,
        protected readonly PageSizeValidator $pageSizeValidator,
        protected readonly TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation,
    ) {
    }

    public function createStoreConnection(Argument $argument): StoreConnection
    {
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

    /**
     * Creates a connection of ProductDeliveryStore nodes carrying the expected pickup date
     * of a single piece of the given product delivered by the given personal pickup transport
     */
    public function createProductDeliveryStoreConnection(
        Argument $argument,
        Transport $transport,
        Product $product,
    ): StoreConnection {
        $storeConnection = $this->createStoreConnection($argument);

        foreach ($storeConnection->getEdges() as $edge) {
            $store = $edge->getNode();

            $edge->setNode(new ProductDeliveryStore(
                $store,
                $this->transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDateForStoreAndProduct(
                    $transport,
                    $product,
                    $this->domain->getId(),
                    $store,
                ),
            ));
        }

        return $storeConnection;
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
