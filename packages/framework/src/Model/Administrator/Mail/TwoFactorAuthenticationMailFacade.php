<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Mail;

use Override;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class TwoFactorAuthenticationMailFacade implements AuthCodeMailerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Mail\TwoFactorAuthenticationMail $twoFactorAuthenticationMail
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly TwoFactorAuthenticationMail $twoFactorAuthenticationMail,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    #[Override]
    public function sendAuthCode(TwoFactorInterface $administrator): void
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE,
            $this->domain->getId(),
        );
        $this->sendMail($mailTemplate, $administrator);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $twoFactorUser
     */
    public function sendMail(MailTemplate $mailTemplate, TwoFactorInterface $twoFactorUser): void
    {
        $messageData = $this->twoFactorAuthenticationMail->createMessage($mailTemplate, $twoFactorUser);

        $this->mailer->sendForDomain($messageData, $this->domain->getId());
    }
}
