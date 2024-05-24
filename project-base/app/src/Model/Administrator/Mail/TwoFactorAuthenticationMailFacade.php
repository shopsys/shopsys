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
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \App\Model\Administrator\Mail\TwoFactorAuthenticationMail $twoFactorAuthenticationMail
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly Mailer $mailer,
        private readonly MailTemplateFacade $mailTemplateFacade,
        private readonly TwoFactorAuthenticationMail $twoFactorAuthenticationMail,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function sendAuthCode(TwoFactorInterface $administrator): void
    {
        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->mailTemplateFacade->get(
            TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE,
            $this->domain->getId(),
        );
        $messageData = $this->twoFactorAuthenticationMail->createMessage($mailTemplate, $administrator);
        $this->mailer->sendForDomain($messageData, $this->domain->getId());
    }
}
