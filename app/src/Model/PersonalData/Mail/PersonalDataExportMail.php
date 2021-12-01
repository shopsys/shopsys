<?php

declare(strict_types=1);

namespace App\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail as BasePersonalDataExportMail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory)
 * @method \Shopsys\FrameworkBundle\Model\Mail\MessageData createMessage(\App\Model\Mail\MailTemplate $template, \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest)
 */
class PersonalDataExportMail extends BasePersonalDataExportMail
{
    /**
     * @param string $hash
     * @return string
     */
    protected function getVariablePersonalDataAccessUrl($hash)
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $hash,
        ];

        return $router->generate(
            'front_export_personal_data',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
