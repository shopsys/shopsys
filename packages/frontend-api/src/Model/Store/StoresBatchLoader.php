<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class StoresBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    /**
     * @param int[] $storeIds
     */
    public function loadByIds(array $storeIds): Promise
    {
        return $this->promiseAdapter->all($this->storeFacade->getStoresByIds($storeIds));
    }
}
