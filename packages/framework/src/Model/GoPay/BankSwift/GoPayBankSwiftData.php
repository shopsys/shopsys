<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

class GoPayBankSwiftData
{
    /**
     * @var string|null
     */
    public $swift;

    /**
     * @var \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
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
