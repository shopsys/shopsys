<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;

/**
 * @property \App\Model\Transport\Transport[] $transports
 */
class PaymentData extends BasePaymentData
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public $goPayPaymentMethod;

    /**
     * @var bool
     */
    public $hiddenByGoPay;

    public function __construct()
    {
        parent::__construct();

        $this->type = Payment::TYPE_BASIC;
    }
}
