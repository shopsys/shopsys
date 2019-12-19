<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTypeInterface;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordMail implements MailTypeInterface, MessageFactoryInterface
{
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_NEW_PASSWORD_URL = '{new_password_url}';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
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
    public function getBodyVariables()
    {
        return [
            self::VARIABLE_EMAIL,
            self::VARIABLE_NEW_PASSWORD_URL,
        ];
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
    public function getRequiredBodyVariables()
    {
        return [
            self::VARIABLE_NEW_PASSWORD_URL,
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
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $customerUser)
    {
        return new MessageData(
            $customerUser->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $customerUser->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $customerUser->getDomainId()),
            $this->getBodyValuesIndexedByVariableName($customerUser),
            $this->getSubjectValuesIndexedByVariableName($customerUser)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return string[]
     */
    protected function getBodyValuesIndexedByVariableName(CustomerUser $customerUser)
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_NEW_PASSWORD_URL => $this->getVariableNewPasswordUrl($customerUser),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return string
     */
    protected function getVariableNewPasswordUrl(CustomerUser $customerUser)
    {
        $router = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

        $routeParameters = [
            'email' => $customerUser->getEmail(),
            'hash' => $customerUser->getResetPasswordHash(),
        ];

        return $router->generate(
            'front_registration_set_new_password',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(CustomerUser $customerUser)
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser);
    }
}
