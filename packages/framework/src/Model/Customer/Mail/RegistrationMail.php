<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationMail implements MessageFactoryInterface
{
    public const VARIABLE_FIRST_NAME = '{first_name}';
    public const VARIABLE_LAST_NAME = '{last_name}';
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_URL = '{url}';
    public const VARIABLE_LOGIN_PAGE = '{login_page}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(protected readonly Setting $setting, protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $mailTemplate, $customerUser)
    {
        return new MessageData(
            $customerUser->getEmail(),
            $mailTemplate->getBccEmail(),
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $customerUser->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $customerUser->getDomainId()),
            $this->getVariablesReplacements($customerUser)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return array
     */
    protected function getVariablesReplacements(CustomerUser $customerUser)
    {
        $router = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

        return [
            self::VARIABLE_FIRST_NAME => htmlspecialchars($customerUser->getFirstName(), ENT_QUOTES),
            self::VARIABLE_LAST_NAME => htmlspecialchars($customerUser->getLastName(), ENT_QUOTES),
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_URL => $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::VARIABLE_LOGIN_PAGE => $router->generate('front_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }
}
