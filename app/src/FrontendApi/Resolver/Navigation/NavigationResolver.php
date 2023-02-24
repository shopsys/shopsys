<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Navigation;

use App\Model\Navigation\NavigationItemFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class NavigationResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var \App\Model\Navigation\NavigationItemFacade
     */
    protected NavigationItemFacade $navigationItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        NavigationItemFacade $navigationItemFacade,
        Domain $domain
    ) {
        $this->navigationItemFacade = $navigationItemFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Navigation\NavigationItemDetail[]
     */
    public function resolveNavigation(): array
    {
        return $this->navigationItemFacade->getOrderedNavigationItemDetails($this->domain->getCurrentDomainConfig());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveNavigation' => 'resolveNavigation'];
    }
}
