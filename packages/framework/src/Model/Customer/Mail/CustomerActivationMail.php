<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerActivationMail implements MessageFactoryInterface
{
    public const string CUSTOMER_ACTIVATION_NAME = 'customer_activation';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_ACTIVATION_URL = '{activation_url}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
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
            $this->getSubjectValuesIndexedByVariableName($customerUser),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string[]
     */
    protected function getBodyValuesIndexedByVariableName(CustomerUser $customerUser): array
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_ACTIVATION_URL => $this->getVariableNewPasswordUrl($customerUser),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    protected function getVariableNewPasswordUrl(CustomerUser $customerUser): string
    {
        $router = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

        $routeParameters = [
            'email' => $customerUser->getEmail(),
            'hash' => $customerUser->getResetPasswordHash(),
        ];

        return $router->generate(
            'front_customer_activation',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(CustomerUser $customerUser): array
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser);
    }
}
