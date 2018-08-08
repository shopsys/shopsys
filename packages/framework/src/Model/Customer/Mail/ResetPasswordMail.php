<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTypeInterface;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordMail implements MailTypeInterface, MessageFactoryInterface
{
    const VARIABLE_EMAIL = '{email}';
    const VARIABLE_NEW_PASSWORD_URL = '{new_password_url}';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    public function __construct(
        Setting $setting,
        DomainRouterFactory $domainRouterFactory
    ) {
        $this->setting = $setting;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @return string[]
     */
    public function getBodyVariables(): array
    {
        return [
            self::VARIABLE_EMAIL,
            self::VARIABLE_NEW_PASSWORD_URL,
        ];
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
    public function getRequiredBodyVariables(): array
    {
        return [
            self::VARIABLE_NEW_PASSWORD_URL,
        ];
    }

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables(): array
    {
        return [];
    }
    
    public function createMessage(MailTemplate $template, \Shopsys\FrameworkBundle\Model\Customer\User $user): \Shopsys\FrameworkBundle\Model\Mail\MessageData
    {
        return new MessageData(
            $user->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $user->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $user->getDomainId()),
            $this->getBodyValuesIndexedByVariableName($user),
            $this->getSubjectValuesIndexedByVariableName($user)
        );
    }

    /**
     * @return string[]
     */
    private function getBodyValuesIndexedByVariableName(User $user): array
    {
        return [
            self::VARIABLE_EMAIL => $user->getEmail(),
            self::VARIABLE_NEW_PASSWORD_URL => $this->getVariableNewPasswordUrl($user),
        ];
    }

    private function getVariableNewPasswordUrl(User $user): string
    {
        $router = $this->domainRouterFactory->getRouter($user->getDomainId());

        $routeParameters = [
            'email' => $user->getEmail(),
            'hash' => $user->getResetPasswordHash(),
        ];

        return $router->generate(
            'front_registration_set_new_password',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @return string[]
     */
    private function getSubjectValuesIndexedByVariableName(User $user): array
    {
        return $this->getBodyValuesIndexedByVariableName($user);
    }
}
