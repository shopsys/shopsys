<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Localization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository;

class IntlCurrencyRepositoryTest extends TestCase
{
    /**
     * @param mixed $currencyCode
     */
    #[DataProvider('getSupportedCurrencyCodes')]
    public function testGetSupportedCurrencies($currencyCode)
    {
        $intlCurrencyRepository = new IntlCurrencyRepository();
        $intlCurrencyRepository->get($currencyCode);
    }

    /**
     * @return string[][]
     */
    public static function getSupportedCurrencyCodes()
    {
        $data = [];

        foreach (IntlCurrencyRepository::SUPPORTED_CURRENCY_CODES as $currencyCode) {
            $data[] = ['currencyCode' => $currencyCode];
        }

        return $data;
    }
}
