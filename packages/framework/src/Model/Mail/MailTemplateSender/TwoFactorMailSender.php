<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\TwoFactorAuthenticationMail;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\TwoFactorAuthenticationMailFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

class TwoFactorMailSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly TwoFactorAuthenticationMailFacade $twoFactorAuthenticationMailFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): ?string
    {
        return null;
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return $mailTemplate->getName() === TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE;
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $this->twoFactorAuthenticationMailFacade->sendMail($mailTemplate, new DummyTwoFactorUser($mailTo));
    }
}
