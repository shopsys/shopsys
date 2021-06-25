<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Navigation;

use App\Model\Navigation\NavigationItemFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class NavigationResolver implements ResolverInterface, AliasedInterface
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
    public function resolve(): array
    {
        return $this->navigationItemFacade->getOrderedNavigationItemDetails($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'navigationResolver'];
    }
}
