<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Localization;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository;

class IntlCurrencyRepositoryTest extends TestCase
{
    /**
     * @dataProvider getSupportedCurrencyCodes
     * @param mixed $currencyCode
     */
    public function testGetSupportedCurrencies($currencyCode): void
    {
        $intlCurrencyRepository = new IntlCurrencyRepository();
        $intlCurrencyRepository->get($currencyCode);
    }

    /**
     * @return string[][]
     */
    public function getSupportedCurrencyCodes(): array
    {
        $data = [];

        foreach (IntlCurrencyRepository::SUPPORTED_CURRENCY_CODES as $currencyCode) {
            $data[] = ['currencyCode' => $currencyCode];
        }

        return $data;
    }
}
