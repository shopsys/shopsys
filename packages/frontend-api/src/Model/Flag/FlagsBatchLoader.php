<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Flag;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagsBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly Domain $domain,
        protected readonly FlagFacade $flagFacade,
    ) {
    }

    /**
     * @param int[][] $flagsIds
     */
    public function loadByIds(array $flagsIds): Promise
    {
        $flagsIndexedById = $this->flagFacade->getByIds(array_merge(...$flagsIds), $this->domain->getCurrentDomainConfig());

        return $this->promiseAdapter->all(array_map(
            static fn (array $flagIds) => array_values(array_intersect_key($flagsIndexedById, array_flip($flagIds))),
            $flagsIds,
        ));
    }
}
