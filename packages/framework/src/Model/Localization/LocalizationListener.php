<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalizationListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();

            if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
                $request->setLocale($this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault());
            } else {
                $request->setLocale($this->domain->getLocale());
            }
        }
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before the default Locale listener
            // see: http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
