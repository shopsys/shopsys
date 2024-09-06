<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use App\Model\Transport\Transport;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Store\StoresFilterOptions;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class StoresQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function storesQuery(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $domainId = $this->domain->getId();

        /** @var string|null $searchText */
        $searchText = $argument->offsetGet('searchText');

        $filterOptions = new StoresFilterOptions(
            searchText: $searchText,
        );

        $paginator = new Paginator(function ($offset, $limit) use ($domainId, $filterOptions) {
            return $this->storeFacade->getStoresByDomainId($domainId, $limit, $offset, $filterOptions);
        });

        $storesCount = $this->storeFacade->getStoresCountByDomainId($domainId, $filterOptions);

        return $paginator->auto($argument, $storesCount);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object|null
     */
    public function storesByTransportQuery(Transport $transport, Argument $argument)
    {
        if ($transport->isPersonalPickup()) {
            return $this->storesQuery($argument);
        }

        return null;
    }
}
