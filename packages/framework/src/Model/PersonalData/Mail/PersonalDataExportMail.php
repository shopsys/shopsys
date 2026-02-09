<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataExportFacade;

class PersonalDataExportMail implements MessageFactoryInterface
{
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_URL = '{url}';
    public const VARIABLE_DOMAIN = '{domain}';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly PersonalDataExportFacade $personalDataExportFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest
     */
    #[Override]
    public function createMessage(
        MailTemplate $template,
        $personalDataAccessRequest,
    ): MessageData {
        return new MessageData(
            $personalDataAccessRequest->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $this->domain->getId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $this->domain->getId()),
            $this->getBodyValuesIndexedByVariableName(
                $this->getVariablePersonalDataAccessUrl(
                    $personalDataAccessRequest->getHash(),
                ),
                $personalDataAccessRequest->getEmail(),
                $this->domain->getName(),
            ),
            $this->getSubjectValuesIndexedByVariableName($this->domain->getName()),
        );
    }

    protected function getBodyValuesIndexedByVariableName(string $url, string $email, string $domainName): array
    {
        return [
            self::VARIABLE_URL => $url,
            self::VARIABLE_EMAIL => htmlspecialchars($email, ENT_QUOTES),
            self::VARIABLE_DOMAIN => htmlspecialchars($domainName, ENT_QUOTES),
        ];
    }

    protected function getSubjectValuesIndexedByVariableName(string $domainName): array
    {
        return [
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }

    protected function getVariablePersonalDataAccessUrl(string $hash): string
    {
        return $this->personalDataExportFacade->getPersonalDataExportLink($hash);
    }
}
