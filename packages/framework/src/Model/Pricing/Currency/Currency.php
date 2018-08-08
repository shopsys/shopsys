<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="currencies")
 * @ORM\Entity
 */
class Currency
{
    const CODE_CZK = 'CZK';
    const CODE_EUR = 'EUR';

    const DEFAULT_EXCHANGE_RATE = 1;

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

    public function __construct(CurrencyData $currencyData)
    {
        $this->name = $currencyData->name;
        $this->code = $currencyData->code;
        $this->exchangeRate = $currencyData->exchangeRate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getExchangeRate(): string
    {
        return $this->exchangeRate;
    }

    public function getReversedExchangeRate(): string
    {
        return 1 / $this->exchangeRate;
    }
    
    public function setExchangeRate(string $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function edit(CurrencyData $currencyData): void
    {
        $this->name = $currencyData->name;
        $this->code = $currencyData->code;
    }
}
