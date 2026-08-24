<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ContactForm;

use Exception;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade;
use Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum;

class ContactFormMutation extends AbstractMutation
{
    public function __construct(
        protected readonly ContactFormFacade $contactFormFacade,
        protected readonly LoggerInterface $logger,
        protected readonly FormSpamProtectionFacade $formSpamProtectionFacade,
    ) {
    }

    public function contactFormMutation(Argument $argument, InputValidator $validator): bool
    {
        if ($this->formSpamProtectionFacade->shouldDiscardSubmission($argument['input'], SpamProtectedFormEnum::CONTACT_FORM)) {
            // the same result as for a successful submission is returned on purpose, so that a bot cannot tell it was detected
            return true;
        }

        $validator->validate();

        $contactFormData = $this->createContactFormDataFromArgument($argument);

        try {
            $this->contactFormFacade->sendMail($contactFormData);
        } catch (Exception $ex) {
            $this->logger->error(
                'Email was not sent from contact form',
                [
                    'error' => $ex->getMessage(),
                ],
            );

            return false;
        }

        return true;
    }

    protected function createContactFormDataFromArgument(Argument $argument): ContactFormData
    {
        $contactFormData = new ContactFormData();

        $contactFormData->name = $argument['input']['name'];
        $contactFormData->email = $argument['input']['email'];
        $contactFormData->message = $argument['input']['message'];

        return $contactFormData;
    }
}
