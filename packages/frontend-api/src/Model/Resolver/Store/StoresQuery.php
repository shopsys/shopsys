<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Store\StoreConnection;
use Shopsys\FrontendApiBundle\Model\Store\StoreConnectionFactory;

class StoresQuery extends AbstractQuery
{
    public function __construct(
        protected readonly StoreConnectionFactory $storeConnectionFactory,
    ) {
    }

    public function storesQuery(
        Argument $argument,
    ): StoreConnection|Promise {
        return $this->storeConnectionFactory->createStoreConnection($argument);
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
