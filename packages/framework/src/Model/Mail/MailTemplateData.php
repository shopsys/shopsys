<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null
     */
    public $orderStatus;

    /**
     * It's used only for creating by administrator, not for editing!
     *
     * @var int|null
     */
    public $domainId;

    public function __construct()
    {
        $this->sendMail = false;
    }
}
