<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Flag;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagsBatchLoader
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
     * @var \App\FrontendApi\Model\Flag\FlagFacade
     */
    private FlagFacade $flagFacade;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Flag\FlagFacade $flagFacade
     */
    public function __construct(PromiseAdapter $promiseAdapter, Domain $domain, FlagFacade $flagFacade)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->domain = $domain;
        $this->flagFacade = $flagFacade;
    }

    /**
     * @param int[][] $flagsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByIds(array $flagsIds): Promise
    {
        return $this->promiseAdapter->all($this->flagFacade->getFlagsByIds($flagsIds, $this->domain->getCurrentDomainConfig()));
    }
}
