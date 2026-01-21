<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;

/**
 * @method __construct(string $name, int $domainId, \App\Model\Mail\MailTemplateData $mailTemplateData)
 * @property \App\Model\Order\Status\OrderStatus|null $orderStatus
 * @method edit(\App\Model\Mail\MailTemplateData $mailTemplateData)
 * @method \App\Model\Order\Status\OrderStatus|null getOrderStatus()
 */
#[ORM\Table(name: 'mail_templates')]
#[ORM\UniqueConstraint(name: 'name_domain', columns: ['name', 'domain_id'])]
#[ORM\Entity]
class MailTemplate extends BaseMailTemplate
{
}
