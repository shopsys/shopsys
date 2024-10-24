<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Brand;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly Domain $domain,
        protected readonly BrandFacade $brandFacade,
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
