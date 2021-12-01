<?php

declare(strict_types=1);

namespace App\Model\GoPay\BankSwift;

class GoPayBankSwiftData
{
    /**
     * @var string|null
     */
    public $swift;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public $goPayPaymentMethod;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $imageNormalUrl;

    /**
     * @var string|null
     */
    public $imageLargeUrl;

    /**
     * @var bool|null
     */
    public $isOnline;
}
