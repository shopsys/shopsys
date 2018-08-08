<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

class MessageData
{
    /**
     * @var string
     */
    public $toEmail;

    /**
     * @var string|null
     */
    public $bccEmail;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $fromEmail;

    /**
     * @var string
     */
    public $fromName;

    /**
     * @var string
     */
    public $variablesReplacementsForSubject;

    /**
     * @var string[]
     */
    public $variablesReplacementsForBody;

    /**
     * @var string[]
     */
    public $attachmentsFilepaths;

    /**
     * @var string|null
     */
    public $replyTo;

    /**
     * @param string|null $bccEmail
     * @param string[] $variablesReplacementsForBody
     * @param string[] $variablesReplacementsForSubject
     * @param string[] $attachmentsFilepaths
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
        array $attachmentsFilepaths = [],
        ?string $replyTo = null
    ) {
        $this->toEmail = $toEmail;
        $this->bccEmail = $bccEmail;
        $this->body = $body;
        $this->subject = $subject;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->variablesReplacementsForBody = $variablesReplacementsForBody;
        if (!empty($variablesReplacementsForSubject)) {
            $this->variablesReplacementsForSubject = $variablesReplacementsForSubject;
        } else {
            $this->variablesReplacementsForSubject = $variablesReplacementsForBody;
        }
        $this->attachmentsFilepaths = $attachmentsFilepaths;
        $this->replyTo = $replyTo;
    }
}
