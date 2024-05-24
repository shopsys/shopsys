<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as MailTemplateDataBase;

/**
 * @property \App\Model\Order\Status\OrderStatus|null $orderStatus
 */
class MailTemplateData extends MailTemplateDataBase
{
}
