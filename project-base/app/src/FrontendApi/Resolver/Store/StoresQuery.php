<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class StoresQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly StoreFacade $storeFacade,
        private readonly Domain $domain,
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

        $paginator = new Paginator(function ($offset, $limit) use ($domainId) {
            return $this->storeFacade->getStoresListEnabledOnDomain($domainId, $limit, $offset);
        });

        $storesCount = $this->storeFacade->getStoresCountEnabledOnDomain($domainId);

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
