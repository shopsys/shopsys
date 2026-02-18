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
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return;
        }

        $event->getResponse()->headers->set('Content-Security-Policy', $this->setting->get(Setting::CSP_HEADER));
    }
}
