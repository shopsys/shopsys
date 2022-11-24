<?php

namespace Tests\FrameworkBundle\Unit\Model\Localization;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository;

class IntlCurrencyRepositoryTest extends TestCase
{
    /**
     * @dataProvider getSupportedCurrencyCodes
     * @param mixed $currencyCode
     */
    public function testGetSupportedCurrencies(mixed $currencyCode): void
    {
        $intlCurrencyRepository = new IntlCurrencyRepository();
        $intlCurrencyRepository->get($currencyCode);
    }

    /**
     * @return array<int, array{currencyCode: string}>
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
