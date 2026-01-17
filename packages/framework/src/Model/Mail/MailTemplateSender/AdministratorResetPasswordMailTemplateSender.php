<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMail;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMailFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

class AdministratorResetPasswordMailTemplateSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Administrator ID');
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return $mailTemplate->getName() === ResetPasswordMail::MAIL_TEMPLATE_NAME;
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $administrator = $this->administratorFacade->getById($entityId);
        $resetPasswordAdministrator = new DummyResetPasswordUser($administrator->getEmail());
        $this->resetPasswordMailFacade->sendMailTemplate($mailTemplate, $resetPasswordAdministrator, $mailTo);
    }
}
