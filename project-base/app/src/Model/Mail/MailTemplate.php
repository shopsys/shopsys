<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;

/**
 * @ORM\Table(
 *     name="mail_templates",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_domain", columns={"name", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @method __construct(string $name, int $domainId, \App\Model\Mail\MailTemplateData $mailTemplateData)
 * @property \App\Model\Order\Status\OrderStatus|null $orderStatus
 * @method edit(\App\Model\Mail\MailTemplateData $mailTemplateData)
 * @method \App\Model\Order\Status\OrderStatus|null getOrderStatus()
 */
class MailTemplate extends BaseMailTemplate
{
}
