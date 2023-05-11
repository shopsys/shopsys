<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

class MessageData
{
    /**
     * @var string
     */
    public string $toEmail;

    /**
     * @var string|null
     */
    public ?string $bccEmail;

    /**
     * @var string
     */
    public string $body;

    /**
     * @var string
     */
    public string $subject;

    /**
     * @var string
     */
    public string $fromEmail;

    /**
     * @var string
     */
    public string $fromName;

    /**
     * @var string[]
     */
    public array $variablesReplacementsForSubject;

    /**
     * @var string[]
     */
    public array $variablesReplacementsForBody;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public array $attachments;

    public ?string $replyTo;

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
        string $toEmail,
        ?string $bccEmail,
        string $body,
        string $subject,
        string $fromEmail,
        string $fromName,
        array $variablesReplacementsForBody = [],
        array $variablesReplacementsForSubject = [],
        array $attachments = [],
        ?string $replyTo = null
    ) {
        $this->toEmail = $toEmail;
        $this->bccEmail = $bccEmail;
        $this->body = $body;
        $this->subject = $subject;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->variablesReplacementsForBody = $variablesReplacementsForBody;

        if (count($variablesReplacementsForSubject) > 0) {
            $this->variablesReplacementsForSubject = $variablesReplacementsForSubject;
        } else {
            $this->variablesReplacementsForSubject = $variablesReplacementsForBody;
        }
        $this->attachments = $attachments;
        $this->replyTo = $replyTo;
    }
}
