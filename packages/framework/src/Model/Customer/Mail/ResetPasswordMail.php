<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Security\NewPasswordUrlProvider;
use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class ResetPasswordMail implements MessageFactoryInterface
{
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_NEW_PASSWORD_URL = '{new_password_url}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly NewPasswordUrlProvider $newPasswordUrlProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    #[Override]
    public function createMessage(MailTemplate $template, $customerUser)
    {
        $domainId = $template->getDomainId();

        return new MessageData(
            $customerUser->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId),
            $this->getBodyValuesIndexedByVariableName($customerUser, $domainId),
            $this->getSubjectValuesIndexedByVariableName($customerUser, $domainId),
        );
    }

    /**
     * @return string[]
     */
    protected function getBodyValuesIndexedByVariableName(ResetPasswordInterface $customerUser, int $domainId)
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_NEW_PASSWORD_URL => $this->newPasswordUrlProvider->getNewPasswordUrl($customerUser, $domainId, 'front_registration_set_new_password'),
        ];
    }

    /**
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(ResetPasswordInterface $customerUser, int $domainId)
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser, $domainId);
    }
}
