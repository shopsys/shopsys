<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalizationListener implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade
     */
    protected $administrationFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     */
    public function __construct(
        Domain $domain,
        Localization $localization,
        AdministrationFacade $administrationFacade
    ) {
        $this->domain = $domain;
        $this->localization = $localization;
        $this->administrationFacade = $administrationFacade;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();

            if ($this->administrationFacade->isInAdmin()) {
                $request->setLocale($this->localization->getAdminLocale());
            } else {
                $request->setLocale($this->domain->getLocale());
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before the default Locale listener
            // see: http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
