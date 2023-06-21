<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\NotificationBar;

use App\Model\NotificationBar\NotificationBarFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class NotificationBarsQuery extends AbstractQuery
{
    /**
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly NotificationBarFacade $notificationBarFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return \App\Model\NotificationBar\NotificationBar[]|null
     */
    public function notificationBarsQuery(): ?array
    {
        return $this->notificationBarFacade->findVisibleAndValidByDomainId($this->domain->getId());
    }
}
