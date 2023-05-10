<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Brand;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BrandsBatchLoader
{
    /**
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\FrontendApi\Model\Brand\BrandFacade
     */
    private BrandFacade $brandFacade;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Brand\BrandFacade $brandFacade
     */
    public function __construct(PromiseAdapter $promiseAdapter, Domain $domain, BrandFacade $brandFacade)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->domain = $domain;
        $this->brandFacade = $brandFacade;
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
