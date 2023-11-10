<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Store;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class StoresBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        private readonly PromiseAdapter $promiseAdapter,
        private readonly StoreFacade $storeFacade,
    ) {
    }

    /**
     * @param int[] $storeIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByIds(array $storeIds): Promise
    {
        return $this->promiseAdapter->all($this->storeFacade->getStoresByIds($storeIds));
    }
}
