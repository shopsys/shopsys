<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $code;

    /**
     * @var string
     */
    public $exchangeRate;

    /**
     * @var int
     */
    public $minFractionDigits;

    /**
     * @var string
     */
    public $roundingType;

    public function __construct()
    {
        $this->exchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
        $this->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
        $this->roundingType = Currency::DEFAULT_ROUNDING_TYPE;
    }
}
