<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

class GoPayPaymentMethodData
{
    /**
     * @var string|null
     */
    public $identifier;

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
     * @var string|null
     */
    public $paymentGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public $currency;

    /**
     * @var int
     */
    public $domainId;
}
