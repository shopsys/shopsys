<?php

declare(strict_types=1);

namespace App\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade as BaseCustomerMailFacade;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method sendRegistrationMail(\App\Model\Customer\User\CustomerUser $customerUser)
 * @property \App\Model\Customer\Mail\CustomerActivationMail $customerActivationMail
 * @method __construct(\Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail $registrationMail, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \App\Model\Customer\Mail\CustomerActivationMail $customerActivationMail)
 * @method sendActivationMail(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerMailFacade extends BaseCustomerMailFacade
{
}
