<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Flag;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class FlagHreflangLinksBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flags
     */
    public function loadByFlags(array $flags): Promise
    {
        return $this->promiseAdapter->all($this->hreflangLinksFacade->getForFlags($flags, $this->domain->getId()));
    }
}
