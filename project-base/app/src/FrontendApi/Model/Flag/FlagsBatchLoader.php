<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Flag;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Flag\FlagFacade $flagFacade
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private Domain $domain,
        private FlagFacade $flagFacade,
    ) {
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
