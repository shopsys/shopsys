<?php

declare(strict_types=1);

namespace App\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerActivationMail as BaseCustomerActivationMail;

/**
 * @method \Shopsys\FrameworkBundle\Model\Mail\MessageData createMessage(\App\Model\Mail\MailTemplate $template, \Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface $customerUser)
 * @method string[] getBodyValuesIndexedByVariableName(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method string[] getSubjectValuesIndexedByVariableName(\App\Model\Customer\User\CustomerUser $customerUser)
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Security\NewPasswordUrlProvider $newPasswordUrlProvider)
 */
class CustomerActivationMail extends BaseCustomerActivationMail
{
}
