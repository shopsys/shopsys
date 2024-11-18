<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Mail\Exception\ResetPasswordHashNotValidException;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordMail implements MessageFactoryInterface
{
    public const MAIL_TEMPLATE_NAME = 'administrator_reset_password';
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_NEW_PASSWORD_URL = '{new_password_url}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $administrator)
    {
        return new MessageData(
            $administrator->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $this->domain->getId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $this->domain->getId()),
            $this->getBodyValuesIndexedByVariableName($administrator),
            $this->getSubjectValuesIndexedByVariableName($administrator),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string[]
     */
    protected function getBodyValuesIndexedByVariableName(Administrator $administrator)
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($administrator->getEmail(), ENT_QUOTES),
            self::VARIABLE_NEW_PASSWORD_URL => $this->getVariableNewPasswordUrl($administrator),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string
     */
    protected function getVariableNewPasswordUrl(Administrator $administrator)
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        if (!$administrator->isResetPasswordHashValid($administrator->getResetPasswordHash())) {
            throw new ResetPasswordHashNotValidException('
                Reset password mail cannot be sent. Administrator with ID "' . $administrator->getId() . '" has invalid reset password hash.
            ');
        }

        $routeParameters = [
            'username' => $administrator->getUsername(),
            'hash' => $administrator->getResetPasswordHash(),
        ];

        return $router->generate(
            'admin_administrator_set-new-password',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(Administrator $administrator)
    {
        return $this->getBodyValuesIndexedByVariableName($administrator);
    }
}
