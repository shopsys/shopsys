<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\NotificationBar;

use App\Model\NotificationBar\NotificationBarFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class NotificationBarsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\NotificationBar\NotificationBarFacade
     */
    private NotificationBarFacade $notificationBarFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(NotificationBarFacade $notificationBarFacade, Domain $domain)
    {
        $this->notificationBarFacade = $notificationBarFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\NotificationBar\NotificationBar[]|null
     */
    public function resolve(): ?array
    {
        return $this->notificationBarFacade->findVisibleAndValidByDomainId($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'notificationBars'];
    }
}
