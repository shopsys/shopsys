<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ResetPasswordMailFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\Mailer
     */
    protected $mailer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail
     */
    protected $resetPasswordMail;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    protected $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail $resetPasswordMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        Mailer $mailer,
        MailTemplateFacade $mailTemplateFacade,
        ResetPasswordMail $resetPasswordMail,
        UploadedFileFacade $uploadedFileFacade
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->resetPasswordMail = $resetPasswordMail;
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendMail(CustomerUser $customerUser)
    {
        $mailTemplate = $this->mailTemplateFacade->get(
            MailTemplate::RESET_PASSWORD_NAME,
            $customerUser->getDomainId()
        );
        $messageData = $this->resetPasswordMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $customerUser->getDomainId());
    }
}
