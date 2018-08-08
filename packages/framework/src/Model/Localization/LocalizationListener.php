<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalizationListener implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    public function __construct(
        Domain $domain,
        Localization $localization
    ) {
        $this->domain = $domain;
        $this->localization = $localization;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();

            if ($this->isAdminRequest($request)) {
                $request->setLocale($this->localization->getAdminLocale());
            } else {
                $request->setLocale($this->domain->getLocale());
            }
        }
    }

    private function isAdminRequest(Request $request): bool
    {
        return preg_match('/^admin_/', $request->attributes->get('_route')) === 1;
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
