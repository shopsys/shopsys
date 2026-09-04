<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use IntlDateFormatter;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GiftVoucherPdfGenerator
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrencyFormatterFactory $currencyFormatterFactory,
        protected readonly CurrencyRepositoryInterface $intlCurrencyRepository,
        protected readonly DateTimeFormatterInterface $dateTimeFormatter,
        protected readonly TranslatorInterface $translator,
        protected readonly string $logoFilepath,
        protected readonly string $backgroundFilepath,
        protected readonly string $fontDir,
    ) {
    }

    public function generatePdfContent(GiftVoucher $giftVoucher): string
    {
        $domainId = $giftVoucher->getDomainId();
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $locale = $domainConfig->getLocale();
        $formattedValueParts = $this->splitFormattedValue($this->formatValue($giftVoucher, $locale));

        $document = $this->createDocument();
        $document->renderVoucher([
            'title' => $this->translator->trans('Gift voucher', [], 'messages', $locale),
            'valueLabel' => $this->translator->trans('Voucher value', [], 'messages', $locale),
            'valuePrefix' => $formattedValueParts['prefix'],
            'valueMain' => $formattedValueParts['main'],
            'valueSuffix' => $formattedValueParts['suffix'],
            'backgroundFilepath' => $this->backgroundFilepath,
            'logoFilepath' => is_file($this->logoFilepath) ? $this->logoFilepath : null,
            'shopName' => $domainConfig->getName(),
            'shopUrl' => $this->getShopUrl($domainId),
            'codeLabel' => $this->translator->trans('Code for redemption', [], 'messages', $locale),
            'codeChunks' => str_split($giftVoucher->getCode(), 4),
            'validityLabel' => $this->translator->trans('Valid until', [], 'messages', $locale),
            'validityDate' => (string)$this->dateTimeFormatter->format(
                $giftVoucher->getValidUntil(),
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE,
                $locale,
            ),
            'redemptionLabel' => $this->translator->trans('Redemption:', [], 'messages', $locale),
            'redemptionText' => $this->translator->trans(
                'enter the code in the cart into the field for discount coupons and gift vouchers.',
                [],
                'messages',
                $locale,
            ),
            'conditionsTitle' => $this->translator->trans('Conditions', [], 'messages', $locale),
            'conditionsText' => $this->translator->trans(
                'The gift voucher applies to all goods and services. The gift voucher cannot be redeemed after the stated validity date; after the validity expires, its value is forfeited without any right to compensation. The voucher cannot be exchanged for cash. The goods or services must be of equal or higher value.',
                [],
                'messages',
                $locale,
            ),
        ]);

        return $document->Output('S');
    }

    protected function createDocument(): GiftVoucherPdfDocument
    {
        return new GiftVoucherPdfDocument($this->fontDir);
    }

    protected function formatValue(GiftVoucher $giftVoucher, string $locale): string
    {
        $value = $giftVoucher->getValueWithVat();
        $currency = $this->currencyFacade->getByCode($giftVoucher->getCurrencyCode());
        $isWholeValue = $value->round(0)->equals($value);
        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndMinFractionDigits(
            $locale,
            $isWholeValue ? 0 : $currency->getMinFractionDigits(),
        );
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $currencyFormatter->format($value->getAmount(), $intlCurrency->getCurrencyCode());
    }

    /**
     * @return array{prefix: string|null, main: string, suffix: string|null}
     */
    protected function splitFormattedValue(string $formattedValue): array
    {
        $prefix = null;
        $suffix = null;
        $main = $formattedValue;

        if (preg_match('~^(?<prefix>[^\d]+?)[\s\x{00A0}\x{202F}]*(?<rest>\d.*)$~u', $main, $matches) === 1) {
            $prefix = $matches['prefix'];
            $main = $matches['rest'];
        }

        if (preg_match('~^(?<rest>.*\d)[\s\x{00A0}\x{202F}]+(?<suffix>[^\d]+)$~u', $main, $matches) === 1) {
            $main = $matches['rest'];
            $suffix = $matches['suffix'];
        }

        return [
            'prefix' => $prefix,
            'main' => $main,
            'suffix' => $suffix,
        ];
    }

    protected function getShopUrl(int $domainId): string
    {
        $shopUrl = $this->domainRouterFactory->getRouter($domainId)->generate(
            'front_homepage',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return rtrim((string)preg_replace('~^https?://~', '', $shopUrl), '/');
    }
}
