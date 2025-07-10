<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Product;

class PriceListDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string ACTIVE_SPECIAL_OFFERS_REFERENCE = 'special_offers';
    public const string EXPIRED_BLUE_WEDNESDAY_REFERENCE = 'blue_wednesday';
    public const string ACTIVE_ITEMS_ON_SALE_REFERENCE = 'items_on_sale';
    public const string FUTURE_PROMOTED_PRODUCTS_REFERENCE = 'promoted_products';

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade $priceListFacade
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory $priceListDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceDataFactory $priceListProductPriceDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        private readonly PriceListFacade $priceListFacade,
        private readonly PriceListDataFactory $priceListDataFactory,
        private readonly PriceListProductPriceDataFactory $priceListProductPriceDataFactory,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainConfig->getId(), Vat::class);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Blue wednesday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-11-10 00:00:00');
            $priceListData->validTo = new DateTimeImmutable('2023-11-10 23:59:59');
            $priceListData->priceListProductPricesData = [
                $this->createPriceListProductPriceData('1', '2800', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createPriceListProductPriceData('72', '90', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $priceList = $this->priceListFacade->create($priceListData);
            $this->addReferenceForDomain(self::EXPIRED_BLUE_WEDNESDAY_REFERENCE, $priceList, $domainConfig->getId());

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Items on sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-02-12 06:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-05-10 08:30:00');
            $priceListData->priceListProductPricesData = [
                $this->createPriceListProductPriceData('117', '290', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createPriceListProductPriceData('19', '170', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $priceList = $this->priceListFacade->create($priceListData);
            $this->addReferenceForDomain(self::ACTIVE_ITEMS_ON_SALE_REFERENCE, $priceList, $domainConfig->getId());

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Promoted products', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2083-10-15 00:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-10-15 06:30:00');
            $priceListData->priceListProductPricesData = [
                $this->createPriceListProductPriceData('145', '800', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createPriceListProductPriceData('120', '160', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $priceList = $this->priceListFacade->create($priceListData);
            $this->addReferenceForDomain(self::FUTURE_PROMOTED_PRODUCTS_REFERENCE, $priceList, $domainConfig->getId());

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Special offers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-01-10 08:30:00');
            $priceListData->validTo = new DateTimeImmutable('2084-01-10 08:30:00');
            $priceListData->priceListProductPricesData = [
                $this->createPriceListProductPriceData('27', '42', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createPriceListProductPriceData('28', '50', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createPriceListProductPriceData('54', '10123', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $priceList = $this->priceListFacade->create($priceListData);
            $this->addReferenceForDomain(self::ACTIVE_SPECIAL_OFFERS_REFERENCE, $priceList, $domainConfig->getId());
        }
    }

    /**
     * @param string $productId
     * @param string $priceValue
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData
     */
    private function createPriceListProductPriceData(
        string $productId,
        string $priceValue,
        int $domainId,
        Currency $currency,
        Vat $vat,
    ): PriceListProductPriceData {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
        $priceAmount = $this->priceConverter->convertPriceToInputPriceInDomainDefaultCurrency(
            Money::create($priceValue),
            $currency,
            $vat->getPercent(),
            $domainId,
        );

        return $this->priceListProductPriceDataFactory->create($product, $priceAmount, $domainId);
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CurrencyDataFixture::class,
            ProductDataFixture::class,
            VatDataFixture::class,
        ];
    }
}
