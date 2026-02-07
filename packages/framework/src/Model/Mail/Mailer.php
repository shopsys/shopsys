<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Closure;
use League\Flysystem\FilesystemOperationFailed;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    public const DISABLED_MAILER_DSN = 'null://null';

    public function __construct(
        protected readonly MailerInterface $symfonyMailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly LoggerInterface $logger,
        protected readonly MailEmbedCollector $mailEmbedCollector,
    ) {
    }

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

    protected function getMessageWithReplacedVariables(
        MessageData $messageData,
        int $domainId,
    ): Email {
        $body = $this->replaceVariables(
            $messageData->body,
            $messageData->variablesReplacementsForBody,
        );
        $body = $this->replaceVariableImagesPaths($body);
        $subject = $this->replaceVariables(
            $messageData->subject,
            $messageData->variablesReplacementsForSubject,
        );

        $email = new Email($domainId);

        $body = $this->mailEmbedCollector->setEmbedsToMail($body, $email);

        $email
            ->subject($subject)
            ->from(new Address($messageData->fromEmail, $messageData->fromName))
            ->to(...$messageData->getToEmailAsArray())
            ->text(htmlspecialchars_decode(strip_tags($body)))
            ->html($body);

        $bccEmails = $messageData->getBccEmailAsArray();

        if (count($bccEmails) > 0) {
            $email->bcc(...$bccEmails);
        }

        if ($messageData->replyTo !== null) {
            $email->addReplyTo($messageData->replyTo);
        }

        foreach ($messageData->attachments as $attachment) {
            try {
                $attachmentFilepath = $this->mailTemplateFacade->getMailTemplateAttachmentFilepath($attachment);
                $attachmentContent = file_get_contents($attachmentFilepath);

                if ($attachmentContent === false) {
                    $this->logger->error('Attachment could not be added - reading the file content failed.', [
                        'attachment' => $attachment,
                        'attachmentFilepath' => $attachmentFilepath,
                    ]);

                    continue;
                }
                $email->attach(
                    $attachmentContent,
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
     * @param array<string, string|\Closure> $variablesKeysAndValues
     */
    protected function replaceVariables(string $string, array $variablesKeysAndValues): string
    {
        $resolvedReplacements = [];

        foreach ($variablesKeysAndValues as $key => $value) {
            if (str_contains($string, $key)) {
                $resolvedReplacements[$key] = $value instanceof Closure ? (string)($value() ?? '') : $value;
            }
        }

        return strtr($string, $resolvedReplacements);
    }

    protected function replaceVariableImagesPaths(string $body): string
    {
        $pattern = '/<img\s+([^>]*?)src="([^"]*)"(\s+[^>]*?)path="([^"]*?)"(.*?)>/i';
        $replacement = '<img $1src="$4"$3$5>';

        return preg_replace($pattern, $replacement, $body);
    }
}
