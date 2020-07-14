<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use App\Model\NotificationBar\NotificationBarFacade;
use Symfony\Component\HttpFoundation\Response;

class NotificationBarController extends FrontBaseController
{
    /**
     * @var \App\Model\NotificationBar\NotificationBarFacade
     */
    private $notificationBarFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        NotificationBarFacade $notificationBarFacade,
        Domain $domain
    ) {
        $this->notificationBarFacade = $notificationBarFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction(): Response
    {
        $notificationBars = $this->notificationBarFacade->findVisibleAndValidByDomainId($this->domain->getId());

        return $this->render('Front/Content/NotificationBar/info.html.twig', [
            'notificationBars' => $notificationBars,
        ]);
    }
}
