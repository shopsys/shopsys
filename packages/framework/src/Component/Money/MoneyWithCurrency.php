<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use Doctrine\ORM\Mapping as ORM;
use Litipk\BigNumbers\Decimal;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

/**
 * @ORM\Embeddable
 */
class MoneyWithCurrency
{
    /**
     * @var \Litipk\BigNumbers\Decimal
     * @ORM\Column(type="big_numbers_decimal", precision=20, scale=6)
     */
    protected Decimal $amount;

    /**
     * @var string
     * @ORM\Column(type="string", length=3, options={"fixed" = true})
     */
    protected string $currency;

    public function __construct(Decimal $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency->getCode();
    }

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency;
    }
}
