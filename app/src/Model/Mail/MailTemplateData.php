<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as MailTemplateDataBase;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class MailTemplateData extends MailTemplateDataBase
{
    /**
     * @var \App\Model\Order\Status\OrderStatus|null
     */
    public ?OrderStatus $orderStatus = null;

    /**
     * It's used only for creating by administrator, not for editing!
     *
     * @var int
     */
    public int $domainId;
}
