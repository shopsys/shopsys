<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Mail;

use InvalidArgumentException;
use Override;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class TwoFactorAuthenticationMailFacade implements AuthCodeMailerInterface
{
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
        if (!$administrator instanceof Administrator) {
            throw new InvalidArgumentException(sprintf('The "%s::%s" method supports only instances of "%s" class, but "%s" instance given.', __CLASS__, __METHOD__, Administrator::class, get_class($administrator)));
        }

        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE,
            $this->domain->getFirstDomainIdMatchingAdminSelectedLocale($administrator),
        );
        $this->sendMail($mailTemplate, $administrator);
    }

    public function sendMail(MailTemplate $mailTemplate, TwoFactorInterface $twoFactorUser): void
    {
        $messageData = $this->twoFactorAuthenticationMail->createMessage($mailTemplate, $twoFactorUser);

        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
