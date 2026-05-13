<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AdministratorActivityRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly CurrentAdministrator $currentAdministrator,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
    ) {
    }

    public function updateAdministratorActivity(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        try {
            $administrator = $this->currentAdministrator->getCurrentlyLoggedAdministrator();
        } catch (AdministratorIsNotLoggedException) {
            return;
        }

        $this->administratorActivityFacade->updateCurrentActivity($administrator);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'updateAdministratorActivity',
        ];
    }
}
