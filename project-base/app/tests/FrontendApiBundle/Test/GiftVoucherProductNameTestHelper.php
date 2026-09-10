<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\DataFixtures\Demo\CurrencyDataFixture;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;

final class GiftVoucherProductNameTestHelper
{
    public function __construct(
        private readonly CurrencyFacade $currencyFacade,
        private readonly PriceConverter $priceConverter,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
    ) {
    }

    public function getExpectedGiftVoucherProductName(
        string $productType,
        string $priceCzk,
        string $locale,
        int $domainId,
    ): string {
        $domainCurrencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId)->getCode();
        $currencyCzk = $this->persistentReferenceFacade->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $valueInDomainCurrency = $this->priceConverter
            ->convertPriceToInputPriceInDomainDefaultCurrency(Money::create($priceCzk), $currencyCzk, '0', $domainId)
            ->round(0)
            ->getAmount();

        if ($productType === ProductTypeEnum::TYPE_PRINTED_GIFT_VOUCHER) {
            return t('Printed gift voucher %value% %currency%', [
                '%value%' => $valueInDomainCurrency,
                '%currency%' => $domainCurrencyCode,
            ], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        return t('Electronic gift voucher %value% %currency%', [
            '%value%' => $valueInDomainCurrency,
            '%currency%' => $domainCurrencyCode,
        ], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
    }
}
