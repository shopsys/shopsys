<?php

declare(strict_types=1);

namespace App\Model\Administrator\Mail;

use App\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class TwoFactorAuthenticationMail implements MessageFactoryInterface
{
    public const TWO_FACTOR_AUTHENTICATION_CODE = 'two_factor_authentication_code';
    public const VARIABLE_AUTHENTICATION_CODE = '{authentication_code}';

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private Setting $setting, private Domain $domain)
    {
    }

    /**
     * @param \App\Model\Mail\MailTemplate $template
     * @param \App\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $administrator)
    {
        $domainId = $this->domain->getId();
        return new MessageData(
            $administrator->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId),
            [self::VARIABLE_AUTHENTICATION_CODE => $administrator->getEmailAuthCode()],
        );
    }
}
