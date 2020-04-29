<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 * @property \App\Model\Transport\Transport[]|\Doctrine\Common\Collections\Collection $transports
 * @method \App\Model\Transport\Transport[] getTransports()
 * @method addTransport(\App\Model\Transport\Transport $transport)
 * @method setTransports(\App\Model\Transport\Transport[] $transports)
 * @method removeTransport(\App\Model\Transport\Transport $transport)
 * @method setTranslations(\App\Model\Payment\PaymentData $paymentData)
 * @method setDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method createDomains(\App\Model\Payment\PaymentData $paymentData)
 */
class Payment extends BasePayment
{
    public const TYPE_BASIC = 'basic';
    public const TYPE_GOPAY = 'goPay';

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     *
     * @ORM\ManyToOne(targetEntity="App\Model\GoPay\PaymentMethod\GoPayPaymentMethod")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $goPayPaymentMethod;

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     */
    public function __construct(BasePaymentData $paymentData)
    {
        parent::__construct($paymentData);

        $this->type = $paymentData->type;
        $this->setGoPayPaymentMethod($paymentData);
    }

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     */
    public function edit(BasePaymentData $paymentData)
    {
        parent::edit($paymentData);

        $this->type = $paymentData->type;
        $this->setGoPayPaymentMethod($paymentData);
    }

    /**
     * @return bool
     */
    public function isGoPay(): bool
    {
        return $this->type === self::TYPE_GOPAY;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public function getGoPayPaymentMethod(): ?GoPayPaymentMethod
    {
        return $this->goPayPaymentMethod;
    }

    public function hide(): void
    {
        $this->hidden = true;
    }

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     */
    private function setGoPayPaymentMethod(BasePaymentData $paymentData): void
    {
        $this->goPayPaymentMethod = null;

        if ($this->type === self::TYPE_GOPAY) {
            $this->goPayPaymentMethod = $paymentData->goPayPaymentMethod;
        }
    }
}
