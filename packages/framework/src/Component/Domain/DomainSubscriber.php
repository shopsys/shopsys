<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DomainSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || $this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        try {
            $this->domain->getId();
        } catch (NoDomainSelectedException $exception) {
            $this->domain->switchDomainByRequest($request);
        }
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // Setting domain by request must be done before loading other services (eg.: routing, localization...)
            KernelEvents::REQUEST => [['onKernelRequest', 20000]],
        ];
    }
}
