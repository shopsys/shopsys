<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class DomainAwareSecurityHeadersSetter
{
    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->domain->isHttps()) {
            return;
        }

        // Do not allow to external content from non-HTTPS URLs.
        // Other security features stays as if CSP was not used:
        // - allow inline JavaScript and CSS
        // - allow eval() function in JavaScript
        // - allow data URLs
        $event->getResponse()->headers->set(
            'Content-Security-Policy',
            "default-src https: 'unsafe-inline' 'unsafe-eval' data:"
        );
    }
}
