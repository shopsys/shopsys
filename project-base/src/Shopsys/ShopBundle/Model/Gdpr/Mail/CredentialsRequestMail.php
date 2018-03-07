<?php

namespace Shopsys\ShopBundle\Model\Gdpr\Mail;

use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Router\DomainRouterFactory;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest;
use Shopsys\ShopBundle\Model\Mail\MailTemplate;
use Shopsys\ShopBundle\Model\Mail\MailTypeInterface;
use Shopsys\ShopBundle\Model\Mail\MessageData;
use Shopsys\ShopBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\ShopBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CredentialsRequestMail implements MailTypeInterface, MessageFactoryInterface
{
    const VARIABLE_EMAIL = '{e-mail}';
    const VARIABLE_URL = '{url}';
    const VARIABLE_DOMAIN = '{domain}';

    /**
     * @var \Shopsys\ShopBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\ShopBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\ShopBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    /**
     * @param \Shopsys\ShopBundle\Component\Domain\Domain $domain
     */
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
    public function getSubjectVariables()
    {
        return $this->getBodyVariables();
    }

    /**
     * @return string[]
     */
    public function getBodyVariables()
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
    public function getRequiredSubjectVariables()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables()
    {
        return [
           self::VARIABLE_URL,
        ];
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Mail\MailTemplate $template
     * @param PersonalDataAccessRequest $dataAccessRequest
     * @return \Shopsys\ShopBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $dataAccessRequest)
    {
        return new MessageData(
            $dataAccessRequest->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $this->domain->getId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $this->domain->getId()),
            $this->getBodyValuesIndexedByVariableName($this->getVariableNewPasswordUrl($dataAccessRequest->getHash()), $dataAccessRequest->getEmail(), $this->domain->getName()),
            $this->getSubjectValuesIndexedByVariableName($this->domain->getName())
        );
    }

    /**
     * @param string $url
     * @param string $email
     * @param string $domainName
     * @return array
     */
    private function getBodyValuesIndexedByVariableName($url, $email, $domainName)
    {
        return [
            self::VARIABLE_URL => $url,
            self::VARIABLE_EMAIL => $email,
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }

    /**
     * @param string $domainName
     * @return array
     */
    private function getSubjectValuesIndexedByVariableName($domainName)
    {
        return [
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }
    /**
     * @param \Shopsys\ShopBundle\Model\Customer\User $user
     * @return string
     */
    private function getVariableNewPasswordUrl($hash)
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $hash,
        ];

        return $router->generate(
            'front_gdpr_detail',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
