<?php

declare(strict_types=1);

namespace App\Model\Administrator\Mail;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class TwoFactorAuthenticationMailFacade implements AuthCodeMailerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\Mailer
     */
    private Mailer $mailer;

    /**
     * @var \App\Model\Mail\MailTemplateFacade
     */
    private MailTemplateFacade $mailTemplateFacade;

    /**
     * @var \App\Model\Administrator\Mail\TwoFactorAuthenticationMail
     */
    private TwoFactorAuthenticationMail $twoFactorAuthenticationMail;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \App\Model\Administrator\Mail\TwoFactorAuthenticationMail $twoFactorAuthenticationMail
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        Mailer $mailer,
        MailTemplateFacade $mailTemplateFacade,
        TwoFactorAuthenticationMail $twoFactorAuthenticationMail,
        Domain $domain
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->twoFactorAuthenticationMail = $twoFactorAuthenticationMail;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function sendAuthCode(TwoFactorInterface $administrator): void
    {
        $mailTemplate = $this->mailTemplateFacade->get(
            TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE,
            $this->domain->getId()
        );
        $messageData = $this->twoFactorAuthenticationMail->createMessage($mailTemplate, $administrator);
        $this->mailer->send($messageData);
    }
}
