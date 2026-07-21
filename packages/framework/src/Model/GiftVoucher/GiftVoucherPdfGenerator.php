<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class GiftVoucherPdfGenerator
{
    public function __construct(
        protected readonly Environment $twig,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrencyFormatterFactory $currencyFormatterFactory,
        protected readonly CurrencyRepositoryInterface $intlCurrencyRepository,
        protected readonly string $logoFilepath,
        protected readonly string $backgroundFilepath,
    ) {
    }

    public function generatePdfContent(GiftVoucher $giftVoucher): string
    {
        $domainId = $giftVoucher->getDomainId();
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $formattedValueParts = $this->splitFormattedValue($this->formatValue($giftVoucher, $domainConfig->getLocale()));

        $html = $this->twig->render('@ShopsysFramework/Mail/GiftVoucher/giftVoucherPdf.html.twig', [
            'giftVoucher' => $giftVoucher,
            'shopName' => $domainConfig->getName(),
            'shopUrl' => $this->getShopUrl($domainId),
            'logoDataUri' => $this->findLogoDataUri(),
            'backgroundDataUri' => $this->getBackgroundDataUri(),
            'domainLocale' => $domainConfig->getLocale(),
            'formattedValuePrefix' => $formattedValueParts['prefix'],
            'formattedValueMain' => $formattedValueParts['main'],
            'formattedValueSuffix' => $formattedValueParts['suffix'],
        ]);

        $dompdf = $this->createDompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return (string)$dompdf->output();
    }

    protected function createDompdf(): Dompdf
    {
        $dompdfOptions = new Options();
        $dompdfOptions->setDefaultFont('DejaVu Sans');
        $dompdfOptions->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($dompdfOptions);
        $dompdf->setPaper('A5', 'landscape');

        return $dompdf;
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

    protected function getBackgroundDataUri(): string
    {
        return 'data:image/png;base64,' . base64_encode((string)file_get_contents($this->backgroundFilepath));
    }

    protected function findLogoDataUri(): ?string
    {
        if (!is_file($this->logoFilepath)) {
            return null;
        }

        $logoContent = file_get_contents($this->logoFilepath);

        if ($logoContent === false) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($logoContent);
    }
}
