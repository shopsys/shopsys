<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as BaseMailTemplateData;

/**
 * @ORM\Table(
 *     name="mail_templates",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_domain", columns={"name", "domain_id", "transport_id", "payment_id", "order_stock_status"})
 *     }
 * )
 * @ORM\Entity
 * @method __construct(string $name, int $domainId, \App\Model\Mail\MailTemplateData $mailTemplateData)
 */
class MailTemplate extends BaseMailTemplate
{
    public const ORDER_STOCK_STATUS_NAME = 'order_stock_status';

    /**
     * @var \App\Model\Transport\Transport|null
     * @ORM\ManyToOne(targetEntity="App\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private ?Transport $transport;

    /**
     * @var \App\Model\Payment\Payment|null
     * @ORM\ManyToOne(targetEntity="App\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private ?Payment $payment;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $orderStockStatus;

    /**
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     */
    public function edit(BaseMailTemplateData $mailTemplateData)
    {
        parent::edit($mailTemplateData);
        $this->transport = $mailTemplateData->transport;
        $this->payment = $mailTemplateData->payment;
        $this->orderStockStatus = $mailTemplateData->orderStockStatus;
    }

    /**
     * @return \App\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    /**
     * @return \App\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @return string|null
     */
    public function getOrderStockStatus(): ?string
    {
        return $this->orderStockStatus;
    }
}
