<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Domain;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AdminDomainQueryParameterSubscriber implements EventSubscriberInterface
{
    public const string QUERY_PARAMETER_NAME = 'switchAdminDomainTo';

    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly Domain $domain,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        $domainId = $this->getDomainIdFromRequest($event->getRequest());

        if ($domainId === null || !in_array($domainId, $this->domain->getAllIds(), true)) {
            return;
        }

        $this->adminDomainTabsFacade->setSelectedDomainId($domainId);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 95]],
        ];
    }

    protected function getDomainIdFromRequest(Request $request): ?int
    {
        return $request->query->filter(
            self::QUERY_PARAMETER_NAME,
            null,
            FILTER_VALIDATE_INT,
            ['flags' => FILTER_NULL_ON_FAILURE],
        );
    }
}
