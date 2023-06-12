<?php

declare(strict_types=1);

namespace App\Model\PersonalData\Mail;

use App\Model\PersonalData\PersonalDataExportFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail as BasePersonalDataExportMail;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method \Shopsys\FrameworkBundle\Model\Mail\MessageData createMessage(\App\Model\Mail\MailTemplate $template, \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest)
 */
class PersonalDataExportMail extends BasePersonalDataExportMail
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\PersonalData\PersonalDataExportFacade $personalDataExportFacade
     */
    public function __construct(
        Domain $domain,
        Setting $setting,
        DomainRouterFactory $domainRouterFactory,
        private PersonalDataExportFacade $personalDataExportFacade,
    ) {
        parent::__construct($domain, $setting, $domainRouterFactory);
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function getVariablePersonalDataAccessUrl($hash)
    {
        return $this->personalDataExportFacade->getPersonalDataExportLink($hash);
    }
}
