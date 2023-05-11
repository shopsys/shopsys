<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class CustomerMailFacade
{
    protected Mailer $mailer;

    protected MailTemplateFacade $mailTemplateFacade;

    protected RegistrationMail $registrationMail;

    protected UploadedFileFacade $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail $registrationMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        Mailer $mailer,
        MailTemplateFacade $mailTemplateFacade,
        RegistrationMail $registrationMail,
        UploadedFileFacade $uploadedFileFacade
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->registrationMail = $registrationMail;
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendRegistrationMail(CustomerUser $customerUser)
    {
        $mailTemplate = $this->mailTemplateFacade->get(
            MailTemplate::REGISTRATION_CONFIRM_NAME,
            $customerUser->getDomainId()
        );
        $messageData = $this->registrationMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $customerUser->getDomainId());
    }
}
