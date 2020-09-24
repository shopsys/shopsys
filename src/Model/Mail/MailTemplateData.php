<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as MailTemplateDataBase;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class MailTemplateData extends MailTemplateDataBase
{
    /**
     * @var \App\Model\Transport\Transport|null
     */
    public ?Transport $transport = null;

    /**
     * @var \App\Model\Payment\Payment|null
     */
    public ?Payment $payment = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null
     */
    public ?OrderStatus $orderStatus = null;

    /**
     * It's used only for creating by administrator, not for editing!
     *
     * @var int
     */
    public int $domainId;
}
