<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class NumberFormatHelper extends Module
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory
     */
    private $currencyFormatterFactory;

    /**
     * @var \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface
     */
    private $intlCurrencyRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension
     */
    private $numberFormatterExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    private $priceConverter;

    /**
     * @var \CommerceGuys\Intl\Formatter\NumberFormatter
     */
    private $numberFormatter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    /**
     * @var \Tests\ShopBundle\Test\Codeception\Helper\LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @param \Codeception\TestInterface $test
     */
    public function _before(TestInterface $test): void
    {
        /** @var \Tests\ShopBundle\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Tests\ShopBundle\Test\Codeception\Helper\LocalizationHelper $localizationHelper */
        $localizationHelper = $this->getModule(LocalizationHelper::class);
        $this->localizationHelper = $localizationHelper;
        $this->currencyFacade = $symfonyHelper->grabServiceFromContainer(CurrencyFacade::class);
        $this->currencyFormatterFactory = $symfonyHelper->grabServiceFromContainer(CurrencyFormatterFactory::class);
        $this->intlCurrencyRepository = $symfonyHelper->grabServiceFromContainer(CurrencyRepositoryInterface::class);
        $this->numberFormatterExtension = $symfonyHelper->grabServiceFromContainer(NumberFormatterExtension::class);
        $this->priceConverter = $symfonyHelper->grabServiceFromContainer(PriceConverter::class);
        $this->numberFormatter = new NumberFormatter(new NumberFormatRepository());
        $this->rounding = $symfonyHelper->grabServiceFromContainer(Rounding::class);
    }

    /**
     * @deprecated test is deprecated and will be removed in the next major.
     *
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceWithCurrencySymbolOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->create($firstDomainLocale);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVat($price)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }

    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVatByCurrency($price, $firstDomainDefaultCurrency)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }

    /**
     * @deprecated test is deprecated and will be removed in the next major.
     *
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->create($firstDomainLocale);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVat($price)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }

    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVatByCurrency($price, $firstDomainDefaultCurrency)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }

    /**
     * It is not possible to use this method for converting total prices of an order or in cart (because of the price calculation)
     * @param string $price
     * @return string
     */
    public function getPriceWithVatConvertedToDomainDefaultCurrency(string $price): string
    {
        $money = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create($price), Domain::FIRST_DOMAIN_ID);

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
        $formattedNumberWithPercentSymbol = $this->numberFormatterExtension->formatPercent($number, $this->localizationHelper->getAdminLocale());

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
