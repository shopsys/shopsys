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
     * @param string|array<string> $toEmail
     * @param string|array<string>|null $bccEmail
     * @param string[] $variablesReplacementsForBody
     * @param string[] $variablesReplacementsForSubject
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $attachments
     */
    public function __construct(
        public string|array $toEmail,
        public string|array|null $bccEmail,
        public string $body,
        public string $subject,
        public string $fromEmail,
        public string $fromName,
        public array $variablesReplacementsForBody = [],
        array $variablesReplacementsForSubject = [],
        public array $attachments = [],
        public ?string $replyTo = null,
    ) {
        if (count($variablesReplacementsForSubject) > 0) {
            $this->variablesReplacementsForSubject = $variablesReplacementsForSubject;
        } else {
            $this->variablesReplacementsForSubject = $variablesReplacementsForBody;
        }
    }

    /**
     * @return string[]
     */
    public function getToEmailAsArray(): array
    {
        return is_array($this->toEmail) ? $this->toEmail : [$this->toEmail];
    }

    /**
     * @return string[]
     */
    public function getBccEmailAsArray(): array
    {
        if ($this->bccEmail === null) {
            return [];
        }

        return is_array($this->bccEmail) ? $this->bccEmail : [$this->bccEmail];
    }
}
