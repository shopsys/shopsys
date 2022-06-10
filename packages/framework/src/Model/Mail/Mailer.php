<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use League\Flysystem\FileNotFoundException;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Mail\Exception\EmptyMailException;
use Shopsys\FrameworkBundle\Model\Mail\Exception\SendMailFailedException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mailer
{
    public const DISABLED_MAILER_DSN = 'null://null';

    /**
     * @var \Symfony\Component\Mailer\MailerInterface
     */
    protected MailerInterface $symfonyMailer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected MailTemplateFacade $mailTemplateFacade;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param \Symfony\Component\Mailer\MailerInterface $symfonyMailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        MailerInterface $symfonyMailer,
        MailTemplateFacade $mailTemplateFacade,
        LoggerInterface $logger
    ) {
        $this->symfonyMailer = $symfonyMailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->logger = $logger;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     */
    public function send(MessageData $messageData): void
    {
        $message = $this->getMessageWithReplacedVariables($messageData);

        if ($messageData->body === null || $messageData->subject === null) {
            throw new EmptyMailException();
        }

        try {
            $this->symfonyMailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new SendMailFailedException($exception);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     * @return \Symfony\Component\Mime\Email
     */
    protected function getMessageWithReplacedVariables(MessageData $messageData): Email
    {
        $body = $this->replaceVariables(
            $messageData->body,
            $messageData->variablesReplacementsForBody
        );
        $subject = $this->replaceVariables(
            $messageData->subject,
            $messageData->variablesReplacementsForSubject
        );

        $email = new Email();
        $email
            ->subject($subject)
            ->from(new Address($messageData->fromEmail, $messageData->fromName))
            ->to($messageData->toEmail)
            ->text(htmlspecialchars_decode(strip_tags($body)))
            ->html($body);
        if ($messageData->bccEmail !== null) {
            $email->addBcc($messageData->bccEmail);
        }
        if ($messageData->replyTo !== null) {
            $email->addReplyTo($messageData->replyTo);
        }

        foreach ($messageData->attachments as $attachment) {
            try {
                $email->attachFromPath(
                    $this->mailTemplateFacade->getMailTemplateAttachmentFilepath($attachment),
                    $attachment->getNameWithExtension()
                );
            } catch (FileNotFoundException $exception) {
                $this->logger->error('Attachment could not be added because file was not found.', [$exception]);
                continue;
            }
        }

        return $email;
    }

    /**
     * @param string $string
     * @param string[] $variablesKeysAndValues
     * @return string
     */
    protected function replaceVariables(string $string, array $variablesKeysAndValues): string
    {
        return strtr($string, $variablesKeysAndValues);
    }
}
