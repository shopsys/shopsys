<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class TwoFactorAuthenticationMail implements MessageFactoryInterface
{
    public const string TWO_FACTOR_AUTHENTICATION_CODE = 'two_factor_authentication_code';
    public const string VARIABLE_AUTHENTICATION_CODE = '{authentication_code}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $administrator
     */
    #[Override]
    public function createMessage(
        MailTemplate $template,
        $administrator,
    ): MessageData {
        return new MessageData(
            $administrator->getEmailAuthRecipient(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $template->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $template->getDomainId()),
            [
                self::VARIABLE_AUTHENTICATION_CODE => fn () => $administrator->getEmailAuthCode(),
            ],
        );
    }
}
