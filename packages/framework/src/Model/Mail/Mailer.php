<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use League\Flysystem\FilesystemOperationFailed;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    public const DISABLED_MAILER_DSN = 'null://null';

    /**
     * @param \Symfony\Component\Mailer\MailerInterface $symfonyMailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly MailerInterface $symfonyMailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     * @param int $domainId
     */
    public function sendForDomain(MessageData $messageData, int $domainId): void
    {
        $message = $this->getMessageWithReplacedVariables($messageData, $domainId);

        try {
            $this->symfonyMailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('There was a failure while sending emails', [
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\Email
     */
    protected function getMessageWithReplacedVariables(
        MessageData $messageData,
        int $domainId,
    ): Email {
        $body = $this->replaceVariables(
            $messageData->body,
            $messageData->variablesReplacementsForBody,
        );
        $subject = $this->replaceVariables(
            $messageData->subject,
            $messageData->variablesReplacementsForSubject,
        );

        $email = new Email($domainId);
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
                    $attachment->getNameWithExtension(),
                );
            } catch (FilesystemOperationFailed $exception) {
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
