<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalizationListener implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade $administratorLocalizationFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdministrationFacade $administrationFacade,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();

            if ($this->administrationFacade->isInAdmin()) {
                $request->setLocale($this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault());
            } else {
                $request->setLocale($this->domain->getLocale());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before the default Locale listener
            // see: http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
