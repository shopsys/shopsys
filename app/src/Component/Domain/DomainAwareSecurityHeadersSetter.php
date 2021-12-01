<?php

declare(strict_types=1);

namespace App\Component\Domain;

use App\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainAwareSecurityHeadersSetter as BaseDomainAwareSecurityHeadersSetter;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class DomainAwareSecurityHeadersSetter extends BaseDomainAwareSecurityHeadersSetter
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(Domain $domain, Setting $setting)
    {
        parent::__construct($domain);

        $this->setting = $setting;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->domain->isHttps()) {
            return;
        }

        $cspHeaderValue = $this->setting->get(Setting::CSP_HEADER);
        $event->getResponse()->headers->set('Content-Security-Policy', $cspHeaderValue);
    }
}
