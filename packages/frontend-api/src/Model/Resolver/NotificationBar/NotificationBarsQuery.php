<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\NotificationBar;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class NotificationBarsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly NotificationBarFacade $notificationBarFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar[]|null
     */
    public function notificationBarsQuery(): ?array
    {
        return $this->notificationBarFacade->findVisibleAndValidByDomainId($this->domain->getId());
    }
}
