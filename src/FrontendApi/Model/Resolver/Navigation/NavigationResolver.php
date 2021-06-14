<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Navigation;

use App\Model\HorizontalMenu\HorizontalMenuItemFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class NavigationResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemFacade
     */
    protected HorizontalMenuItemFacade $horizontalMenuItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemFacade $horizontalMenuItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        HorizontalMenuItemFacade $horizontalMenuItemFacade,
        Domain $domain
    ) {
        $this->horizontalMenuItemFacade = $horizontalMenuItemFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemDetail[]
     */
    public function resolve(): array
    {
        return $this->horizontalMenuItemFacade->getOrderedHorizontalMenuItemDetails($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'navigationResolver'];
    }
}
