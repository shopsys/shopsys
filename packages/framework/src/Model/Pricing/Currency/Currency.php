<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="currencies")
 * @ORM\Entity
 */
class Currency
{
    public const CODE_CZK = 'CZK';
    public const CODE_EUR = 'EUR';

    public const DEFAULT_EXCHANGE_RATE = '1';
    public const DEFAULT_MIN_FRACTION_DIGITS = 2;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $exchangeRate;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $minFractionDigits;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    public function __construct(CurrencyData $currencyData)
    {
        $this->name = $currencyData->name;
        $this->code = $currencyData->code;
        $this->exchangeRate = $currencyData->exchangeRate;
        $this->minFractionDigits = $currencyData->minFractionDigits;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * @param string $exchangeRate
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return int
     */
    public function getMinFractionDigits(): int
    {
        return $this->minFractionDigits;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    public function edit(CurrencyData $currencyData)
    {
        $this->name = $currencyData->name;
        $this->code = $currencyData->code;
        $this->minFractionDigits = $currencyData->minFractionDigits;
    }
}
