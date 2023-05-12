<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

class MessageData
{
    /**
     * @var string[]
     */
    public array $variablesReplacementsForSubject;

    /**
     * @param string $toEmail
     * @param string|null $bccEmail
     * @param string $body
     * @param string $subject
     * @param string $fromEmail
     * @param string $fromName
     * @param string[] $variablesReplacementsForBody
     * @param string[] $variablesReplacementsForSubject
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $attachments
     * @param string|null $replyTo
     */
    public function __construct(
        public string $toEmail,
        public ?string $bccEmail,
        public string $body,
        public string $subject,
        public string $fromEmail,
        public string $fromName,
        public array $variablesReplacementsForBody = [],
        array $variablesReplacementsForSubject = [],
        public array $attachments = [],
        public ?string $replyTo = null
    ) {
        if (count($variablesReplacementsForSubject) > 0) {
            $this->variablesReplacementsForSubject = $variablesReplacementsForSubject;
        } else {
            $this->variablesReplacementsForSubject = $variablesReplacementsForBody;
        }
    }
}
