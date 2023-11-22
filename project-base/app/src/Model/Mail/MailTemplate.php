<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as BaseMailTemplateData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

/**
 * @ORM\Table(
 *     name="mail_templates",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_domain", columns={"name", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @method __construct(string $name, int $domainId, \App\Model\Mail\MailTemplateData $mailTemplateData)
 */
class MailTemplate extends BaseMailTemplate
{
    public const ORDER_STATUS_NAME = 'order_status';

    /**
     * @var \App\Model\Order\Status\OrderStatus|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private ?OrderStatus $orderStatus;

    /**
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     */
    public function edit(BaseMailTemplateData $mailTemplateData): void
    {
        parent::edit($mailTemplateData);

        $this->orderStatus = $mailTemplateData->orderStatus;
    }

    /**
     * @return \App\Model\Order\Status\OrderStatus|null
     */
    public function getOrderStatus(): ?OrderStatus
    {
        return $this->orderStatus;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}
