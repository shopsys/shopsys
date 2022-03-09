<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\DomainController as BaseDomainController;
use Symfony\Component\HttpFoundation\Response;

class DomainController extends BaseDomainController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function localeTabsAction(): Response
    {
        $domainConfigs = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            if (!isset($domainConfigs[$domainConfig->getLocale()])) {
                $domainConfigs[$domainConfig->getLocale()] = $domainConfig;
            }
        }

        return $this->render('Admin/Inline/Domain/locales.html.twig', [
            'domainConfigs' => $domainConfigs,
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }
}
