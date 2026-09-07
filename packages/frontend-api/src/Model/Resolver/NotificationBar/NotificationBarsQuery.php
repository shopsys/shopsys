<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\NotificationBar;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;
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

    public function notificationBarPlainTextQuery(NotificationBar $notificationBar): string
    {
        return trim(strip_tags(html_entity_decode($notificationBar->getText(), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
