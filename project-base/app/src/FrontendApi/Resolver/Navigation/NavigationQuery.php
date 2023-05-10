<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Navigation;

use App\Model\Navigation\NavigationItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class NavigationQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly NavigationItemFacade $navigationItemFacade,
        private readonly Domain $domain
    ) {
    }

    /**
     * @return \App\Model\Navigation\NavigationItemDetail[]
     */
    public function navigationQuery(): array
    {
        return $this->navigationItemFacade->getOrderedNavigationItemDetails($this->domain->getCurrentDomainConfig());
    }
}
