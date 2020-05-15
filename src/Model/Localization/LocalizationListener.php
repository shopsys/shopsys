<?php

declare(strict_types=1);

namespace App\Model\Localization;

use Shopsys\FrameworkBundle\Model\Localization\LocalizationListener as BaseLocalizationListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @property \App\Component\Domain\Domain $domain
 * @method __construct(\App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade)
 */
class LocalizationListener extends BaseLocalizationListener
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->domain->isOnCdnDomain() === false) {
            parent::onKernelRequest($event);
        }
    }
}
