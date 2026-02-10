<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class SecurityHeadersResponseListener
{
    public function __construct(
        protected readonly Setting $setting,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $response->headers->set('X-Frame-Options', 'sameorigin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Powered-By', 'Shopsys Platform');
        $response->headers->set('Referrer-Policy', 'same-origin');

        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return;
        }

        $cspHeaderValue = $this->sanitizeCspHeaderValue($this->setting->get(Setting::CSP_HEADER));
        $response->headers->set('Content-Security-Policy', $cspHeaderValue);
    }

    protected function sanitizeCspHeaderValue(string $cspHeaderValue): string
    {
        return str_replace(["\r", "\n"], '', $cspHeaderValue);
    }
}
