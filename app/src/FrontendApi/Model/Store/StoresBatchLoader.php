<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Store;

use App\Model\Store\StoreFacade;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class StoresBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        readonly private PromiseAdapter $promiseAdapter,
        readonly private StoreFacade $storeFacade
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
