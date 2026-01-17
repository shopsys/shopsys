<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Domain;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AdminDomainSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly AdminUrlProvider $adminUrlProvider,
        protected readonly Domain $domain,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        $currentUrl = $request->getUri();
        $redirectUrl = $this->getRedirectUrl($request);

        if (str_starts_with($currentUrl, $redirectUrl)) {
            return;
        }

        $queryString = $request->getQueryString();

        if ($queryString !== null) {
            $redirectUrl .= '?' . $queryString;
        }

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // This should run before the DomainSubscriber
            KernelEvents::REQUEST => [['onKernelRequest', 21000]],
        ];
    }

    protected function getRedirectUrl(Request $request): string
    {
        $firstDomainBaseUrl = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getBaseUrl();
        $adminPath = $this->extractAdminPath($request->getPathInfo());

        return rtrim($firstDomainBaseUrl, '/') . $adminPath;
    }

    protected function extractAdminPath(string $path): string
    {
        if (preg_match('~(/' . $this->adminUrlProvider->getAdminUrl() . '.*)~', $path, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
