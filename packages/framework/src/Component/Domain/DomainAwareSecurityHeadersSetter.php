<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class DomainAwareSecurityHeadersSetter
{
    public function __construct(
        protected readonly Setting $setting,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return;
        }

        $cspHeaderValue = $this->sanitizeCspHeaderValue($this->setting->get(Setting::CSP_HEADER));
        $event->getResponse()->headers->set('Content-Security-Policy', $cspHeaderValue);
    }

    protected function sanitizeCspHeaderValue(string $cspHeaderValue): string
    {
        return str_replace(["\r", "\n"], '', $cspHeaderValue);
    }
}
