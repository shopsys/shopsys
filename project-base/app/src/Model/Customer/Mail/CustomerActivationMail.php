<?php

declare(strict_types=1);

namespace App\Model\Customer\Mail;

use App\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerActivationMail implements MessageFactoryInterface
{
    public const CUSTOMER_ACTIVATION_NAME = 'customer_activation';
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_ACTIVATION_URL = '{activation_url}';

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        private Setting $setting,
        private DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \App\Model\Mail\MailTemplate $template
     * @param \App\Model\Customer\User\CustomerUser $customerUser
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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return string[]
     */
    private function getBodyValuesIndexedByVariableName(CustomerUser $customerUser): array
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_ACTIVATION_URL => $this->getVariableNewPasswordUrl($customerUser),
        ];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    private function getVariableNewPasswordUrl(CustomerUser $customerUser): string
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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return string[]
     */
    private function getSubjectValuesIndexedByVariableName(CustomerUser $customerUser): array
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser);
    }
}
