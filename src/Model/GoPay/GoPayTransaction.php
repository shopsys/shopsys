<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\Order\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="gopay_transactions")
 * @ORM\Entity
 */
class GoPayTransaction
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=20)
     */
    private $goPayId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $goPayStatus;

    /**
     * @var \App\Model\Order\Order
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Order", inversedBy="goPayTransactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @param \App\Model\GoPay\GoPayTransactionData $goPayTransactionData
     */
    public function __construct(GoPayTransactionData $goPayTransactionData)
    {
        $this->goPayId = $goPayTransactionData->goPayId;
        $this->goPayStatus = $goPayTransactionData->goPayStatus;
        $this->order = $goPayTransactionData->order;
    }

    /**
     * @return string
     */
    public function getGoPayId(): string
    {
        return $this->goPayId;
    }

    /**
     * @return string|null
     */
    public function getGoPayStatus(): ?string
    {
        return $this->goPayStatus;
    }

    /**
     * @return \App\Model\Order\Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param string|null $goPayStatus
     */
    public function setGoPayStatus(?string $goPayStatus = null): void
    {
        $this->goPayStatus = $goPayStatus;
    }
}
