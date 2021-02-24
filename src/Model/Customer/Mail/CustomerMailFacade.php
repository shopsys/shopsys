<?php

declare(strict_types=1);

namespace App\Model\Customer\Mail;

use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade as BaseCustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method sendRegistrationMail(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerMailFacade extends BaseCustomerMailFacade
{
    /**
     * @var \App\Model\Customer\Mail\CustomerActivationMail
     */
    private CustomerActivationMail $customerActivationMail;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail $registrationMail
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \App\Model\Customer\Mail\CustomerActivationMail $customerActivationMail
     */
    public function __construct(
        Mailer $mailer,
        MailTemplateFacade $mailTemplateFacade,
        RegistrationMail $registrationMail,
        UploadedFileFacade $uploadedFileFacade,
        CustomerActivationMail $customerActivationMail
    ) {
        parent::__construct($mailer, $mailTemplateFacade, $registrationMail, $uploadedFileFacade);

        $this->customerActivationMail = $customerActivationMail;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendActivationMail(CustomerUser $customerUser): void
    {
        $mailTemplate = $this->mailTemplateFacade->get(CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $customerUser->getDomainId());
        $messageData = $this->customerActivationMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->send($messageData);
    }
}
