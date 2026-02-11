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

class CustomerActivationMail implements MessageFactoryInterface
{
    public const string CUSTOMER_ACTIVATION_NAME = 'customer_activation';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_ACTIVATION_URL = '{activation_url}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly NewPasswordUrlProvider $newPasswordUrlProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface $customerUser
     */
    #[Override]
    public function createMessage(MailTemplate $template, mixed $customerUser): MessageData
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
     * @return array<string, \Closure>
     */
    protected function getBodyValuesIndexedByVariableName(ResetPasswordInterface $customerUser, int $domainId): array
    {
        return [
            self::VARIABLE_EMAIL => fn () => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_ACTIVATION_URL => fn () => $this->newPasswordUrlProvider->getNewPasswordUrl($customerUser, $domainId, 'front_registration_set_new_password'),
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getSubjectValuesIndexedByVariableName(
        ResetPasswordInterface $customerUser,
        int $domainId,
    ): array {
        return $this->getBodyValuesIndexedByVariableName($customerUser, $domainId);
    }
}
