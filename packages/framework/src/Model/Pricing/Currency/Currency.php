<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidRoundingTypeException;

#[ORM\Table(name: 'currencies')]
#[ORM\Entity]
class Currency
{
    public const string CODE_CZK = 'CZK';
    public const string CODE_EUR = 'EUR';

    public const string ROUNDING_TYPE_HUNDREDTHS = 'hundredths';
    public const string ROUNDING_TYPE_FIFTIES = 'fifties';
    public const string ROUNDING_TYPE_INTEGER = 'integer';

    public const string DEFAULT_EXCHANGE_RATE = '1';
    public const int DEFAULT_MIN_FRACTION_DIGITS = 2;
    public const string DEFAULT_ROUNDING_TYPE = self::ROUNDING_TYPE_INTEGER;
    public const int DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT = 2;

    public const int MAX_ROUNDING_PLACES_PRICE_WITHOUT_VAT = 6;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50)]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 3, unique: true)]
    protected $code;

    /**
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    protected $exchangeRate;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $minFractionDigits;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 15)]
    protected $roundingType;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $roundingPlacesPriceWithoutVat;

    public function __construct(CurrencyData $currencyData)
    {
        $this->exchangeRate = $currencyData->exchangeRate;
        $this->setData($currencyData);
    }

    public function edit(CurrencyData $currencyData): void
    {
        $this->setData($currencyData);
    }

    protected function setData(CurrencyData $currencyData): void
    {
        $this->name = $currencyData->name;
        $this->code = $currencyData->code;
        $this->minFractionDigits = $currencyData->minFractionDigits;
        $this->setRoundingType($currencyData->roundingType);
        $this->roundingPlacesPriceWithoutVat = $currencyData->roundingPlacesPriceWithoutVat;
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
    public function setExchangeRate($exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return int
     */
    public function getMinFractionDigits()
    {
        return $this->minFractionDigits;
    }

    /**
     * @return string
     */
    public function getRoundingType()
    {
        return $this->roundingType;
    }

    /**
     * @param string $roundingType
     */
    protected function setRoundingType($roundingType): void
    {
        if (in_array($roundingType, $this->getRoundingTypes(), true) !== true) {
            throw new InvalidRoundingTypeException($roundingType);
        }

        $this->roundingType = $roundingType;
    }

    /**
     * @return int
     */
    public function getRoundingPlacesPriceWithoutVat()
    {
        return $this->roundingPlacesPriceWithoutVat;
    }

    /**
     * @return string[]
     */
    protected function getRoundingTypes(): array
    {
        return [
            self::ROUNDING_TYPE_HUNDREDTHS,
            self::ROUNDING_TYPE_FIFTIES,
            self::ROUNDING_TYPE_INTEGER,
        ];
    }
}
