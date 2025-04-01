<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class NumberFormatHelper extends Module
{
    private CurrencyFacade $currencyFacade;

    private NumberFormatterExtension $numberFormatterExtension;

    private PriceConverter $priceConverter;

    private NumberFormatter $numberFormatter;

    private LocalizationHelper $localizationHelper;

    /**
     * @param \Codeception\TestInterface $test
     */
    #[Override]
    public function _before(TestInterface $test): void
    {
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Tests\App\Test\Codeception\Helper\LocalizationHelper $localizationHelper */
        $localizationHelper = $this->getModule(LocalizationHelper::class);
        $this->localizationHelper = $localizationHelper;
        $this->currencyFacade = $symfonyHelper->grabServiceFromContainer(CurrencyFacade::class);
        $this->numberFormatterExtension = $symfonyHelper->grabServiceFromContainer(NumberFormatterExtension::class);
        $this->priceConverter = $symfonyHelper->grabServiceFromContainer(PriceConverter::class);
        $this->numberFormatter = new NumberFormatter(new NumberFormatRepository());
    }

    /**
     * It is not possible to use this method for converting total prices of an order or in cart (because of the price calculation)
     *
     * @param string $price
     * @return string
     */
    public function getPriceWithVatConvertedToDomainDefaultCurrency(string $price): string
    {
        $currency = $this->currencyFacade->getByCode(Currency::CODE_CZK);
        $money = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create($price),
            $currency,
            Domain::FIRST_DOMAIN_ID,
        );

        return $money->getAmount();
    }

    /**
     * @param string $number
     * @param string $locale
     * @return string
     */
    public function getNumberFromLocalizedFormat(string $number, string $locale): string
    {
        return $this->numberFormatter->parse($number, ['locale' => $locale]);
    }

    /**
     * @param string $number
     * @return string
     */
    public function getFormattedPercentAdmin(string $number): string
    {
        $formattedNumberWithPercentSymbol = $this->numberFormatterExtension->formatPercent(
            $number,
            $this->localizationHelper->getAdminLocale(),
        );

        return $this->normalizeSpaces($formattedNumberWithPercentSymbol);
    }

    /**
     * The output of the CurrencyFormatter::format() method may contain non-breaking spaces that are not recognized by Codeception
     * so we need to replace them with regular spaces here.
     * See https://stackoverflow.com/questions/12837682/non-breaking-utf-8-0xc2a0-space-and-preg-replace-strange-behaviour
     *
     * @param string $text
     * @return string
     */
    private function normalizeSpaces(string $text): string
    {
        return preg_replace('~\x{00a0}~siu', ' ', $text);
    }
}
