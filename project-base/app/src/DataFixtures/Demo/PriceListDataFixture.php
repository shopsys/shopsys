<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData;
use Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Product;

class PriceListDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade $priceListFacade
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory $priceListDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceDataFactory $productWithPriceDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        private readonly PriceListFacade $priceListFacade,
        private readonly PriceListDataFactory $priceListDataFactory,
        private readonly ProductWithPriceDataFactory $productWithPriceDataFactory,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainConfig->getId(), Vat::class);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Special offers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-01-10 08:30:00');
            $priceListData->validTo = new DateTimeImmutable('2084-01-10 08:30:00');
            $priceListData->productsWithPrices = [
                $this->createProductWithPriceData('27', '42', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createProductWithPriceData('28', '50', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Blue wednesday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-11-10 00:00:00');
            $priceListData->validTo = new DateTimeImmutable('2023-11-10 23:59:59');
            $priceListData->productsWithPrices = [
                $this->createProductWithPriceData('1', '2800', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createProductWithPriceData('72', '90', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Items on sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-02-12 06:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-05-10 08:30:00');
            $priceListData->productsWithPrices = [
                $this->createProductWithPriceData('117', '290', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createProductWithPriceData('19', '170', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Promoted products', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2083-10-15 00:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-10-15 06:30:00');
            $priceListData->productsWithPrices = [
                $this->createProductWithPriceData('145', '800', $domainConfig->getId(), $currencyCzk, $vat),
                $this->createProductWithPriceData('120', '160', $domainConfig->getId(), $currencyCzk, $vat),
            ];
            $this->priceListFacade->create($priceListData);
        }
    }

    /**
     * @param string $productId
     * @param string $priceValue
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData
     */
    private function createProductWithPriceData(
        string $productId,
        string $priceValue,
        int $domainId,
        Currency $currency,
        Vat $vat,
    ): ProductWithPriceData {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
        $priceAmount = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
            Money::create($priceValue),
            $currency,
            $vat->getPercent(),
            $domainId,
        );

        return $this->productWithPriceDataFactory->create($product, $priceAmount, $domainId);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            CurrencyDataFixture::class,
            ProductDataFixture::class,
            VatDataFixture::class,
        ];
    }
}
