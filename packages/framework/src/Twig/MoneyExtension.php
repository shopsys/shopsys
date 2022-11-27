<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'moneyFormat',
                [$this, 'moneyFormatFilter']
            ),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @param int|null $decimal
     * @param string $decimalPoint
     * @param string $thousandsSeparator
     * @return string
     */
    public function moneyFormatFilter(Money $money, ?int $decimal = null, string $decimalPoint = '.', string $thousandsSeparator = ''): string
    {
        $moneyString = $money->getAmount();

        if ($decimal === null) {
            $decimal = $this->getNumberOfDecimalPlaces($moneyString);
        }

        return number_format((float)$moneyString, $decimal, $decimalPoint, $thousandsSeparator);
    }

    /**
     * @param string $numeric
     * @return int
     */
    protected function getNumberOfDecimalPlaces(string $numeric): int
    {
        $decimalPointPosition = strpos($numeric, '.');

        if ($decimalPointPosition === false) {
            return 0;
        }

        return strlen($numeric) - $decimalPointPosition - 1;
    }
}
