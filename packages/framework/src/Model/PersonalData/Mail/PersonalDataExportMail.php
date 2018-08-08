<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTypeInterface;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonalDataExportMail implements MailTypeInterface, MessageFactoryInterface
{
    const VARIABLE_EMAIL = '{e-mail}';
    const VARIABLE_URL = '{url}';
    const VARIABLE_DOMAIN = '{domain}';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    public function __construct(
        Domain $domain,
        Setting $setting,
        DomainRouterFactory $domainRouterFactory
    ) {
        $this->domain = $domain;
        $this->setting = $setting;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @return string[]
     */
    public function getSubjectVariables(): array
    {
        return $this->getBodyVariables();
    }

    /**
     * @return string[]
     */
    public function getBodyVariables(): array
    {
        return [
            self::VARIABLE_URL,
            self::VARIABLE_EMAIL,
            self::VARIABLE_DOMAIN,
        ];
    }

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables(): array
    {
        return [
            self::VARIABLE_URL,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest
     */
    public function createMessage(MailTemplate $template, $personalDataAccessRequest): \Shopsys\FrameworkBundle\Model\Mail\MessageData
    {
        return new MessageData(
            $personalDataAccessRequest->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $this->domain->getId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $this->domain->getId()),
            $this->getBodyValuesIndexedByVariableName(
                $this->getVariablePersonalDataAccessUrl(
                    $personalDataAccessRequest->getHash()
                ),
                $personalDataAccessRequest->getEmail(),
                $this->domain->getName()
            ),
            $this->getSubjectValuesIndexedByVariableName($this->domain->getName())
        );
    }

    /**
     * @param string $url
     * @param string $email
     * @param string $domainName
     */
    private function getBodyValuesIndexedByVariableName($url, $email, $domainName): array
    {
        return [
            self::VARIABLE_URL => $url,
            self::VARIABLE_EMAIL => $email,
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }

    /**
     * @param string $domainName
     */
    private function getSubjectValuesIndexedByVariableName($domainName): array
    {
        return [
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }

    /**
     * @param string $hash
     */
    private function getVariablePersonalDataAccessUrl($hash): string
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $hash,
        ];

        return $router->generate(
            'front_personal_data_access_export',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
