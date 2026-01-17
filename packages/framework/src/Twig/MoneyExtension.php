<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    /**
     * @return array
     */
    #[Override]
    public function getFilters()
    {
        return [
            new TwigFilter(
                'moneyFormat',
                $this->moneyFormatFilter(...),
            ),
        ];
    }

    /**
     * @return string
     */
    public function moneyFormatFilter(
        Money $money,
        ?int $decimal = null,
        string $decimalPoint = '.',
        string $thousandsSeparator = '',
    ) {
        $moneyString = $money->getAmount();

        if ($decimal === null) {
            $decimal = $this->getNumberOfDecimalPlaces($moneyString);
        }

        return number_format((float)$moneyString, $decimal, $decimalPoint, $thousandsSeparator);
    }

    protected function getNumberOfDecimalPlaces(string $numeric): int
    {
        $decimalPointPosition = strpos($numeric, '.');

        if ($decimalPointPosition === false) {
            return 0;
        }

        return strlen($numeric) - $decimalPointPosition - 1;
    }
}
