<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;

class MailTemplateData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $bccEmail;

    /**
     * @var string|null
     */
    public $subject;

    /**
     * @var string|null
     */
    public $body;

    /**
     * @var bool
     */
    public $sendMail;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public $attachments;

    public function __construct()
    {
        $this->sendMail = false;
        $this->attachments = new UploadedFileData();
    }
}
