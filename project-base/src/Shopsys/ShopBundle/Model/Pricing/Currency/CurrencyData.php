<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyData
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $exchangeRate;

    /**
     * @param string|null $name
     * @param string|null $code
     * @param string $exchangeRate
     */
    public function __construct($name = null, $code = null, $exchangeRate = Currency::DEFAULT_EXCHANGE_RATE)
    {
        $this->name = $name;
        $this->code = $code;
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    public function setFromEntity(Currency $currency)
    {
        $this->name = $currency->getName();
        $this->code = $currency->getCode();
        $this->exchangeRate = $currency->getExchangeRate();
    }
}
