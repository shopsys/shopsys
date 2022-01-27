<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailException;

class ContactMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade
     */
    private ContactFormFacade $contactFormFacade;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade $contactFormFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ContactFormFacade $contactFormFacade,
        LoggerInterface $logger
    ) {
        $this->contactFormFacade = $contactFormFacade;
        $this->logger = $logger;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return bool
     */
    public function contact(Argument $argument, InputValidator $validator): bool
    {
        $validator->validate();

        $contactFormData = $this->createContactFormDataFromArgument($argument);

        try {
            $this->contactFormFacade->sendMail($contactFormData);
        } catch (MailException $ex) {
            $this->logger->error(
                'Email was not sent from contact form',
                [
                    'error' => $ex->getMessage(),
                ]
            );
            return false;
        }

        return true;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData
     */
    private function createContactFormDataFromArgument(Argument $argument): ContactFormData
    {
        $contactFormData = new ContactFormData();

        $contactFormData->name = $argument['input']['name'];
        $contactFormData->email = $argument['input']['email'];
        $contactFormData->message = $argument['input']['message'];

        return $contactFormData;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'contact' => 'contact',
        ];
    }
}
