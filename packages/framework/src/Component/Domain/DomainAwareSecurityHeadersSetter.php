<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class DomainAwareSecurityHeadersSetter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->isHttps()) {
            return;
        }

        $cspHeaderValue = $this->setting->get(Setting::CSP_HEADER);
        $event->getResponse()->headers->set('Content-Security-Policy', $cspHeaderValue);
    }
}
