<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'moneyFormat',
                $this->moneyFormatFilter(...),
            ),
        ];
    }

    public function moneyFormatFilter(
        Money $money,
        ?int $decimal = null,
        string $decimalPoint = '.',
        string $thousandsSeparator = '',
    ): string {
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
