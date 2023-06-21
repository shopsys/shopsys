<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Brand;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BrandsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private Domain $domain,
        private BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param int[] $brandIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByIds(array $brandIds): Promise
    {
        return $this->promiseAdapter->all($this->brandFacade->getByIds($brandIds, $this->domain->getCurrentDomainConfig()));
    }
}
