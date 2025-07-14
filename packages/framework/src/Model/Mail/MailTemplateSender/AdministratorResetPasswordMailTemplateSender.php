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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMailFacade $resetPasswordMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Administrator ID');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return bool
     */
    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return $mailTemplate->getName() === ResetPasswordMail::MAIL_TEMPLATE_NAME;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $administrator = $this->administratorFacade->getById($entityId);
        $resetPasswordAdministrator = new DummyResetPasswordUser($administrator->getEmail());
        $this->resetPasswordMailFacade->sendMailTemplate($mailTemplate, $resetPasswordAdministrator, $mailTo);
    }
}
