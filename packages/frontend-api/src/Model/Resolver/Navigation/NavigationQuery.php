<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Navigation;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class NavigationQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly NavigationItemFacade $navigationItemFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail[]
     */
    public function navigationQuery(): array
    {
        return $this->navigationItemFacade->getOrderedNavigationItemDetails($this->domain->getCurrentDomainConfig());
    }
}
