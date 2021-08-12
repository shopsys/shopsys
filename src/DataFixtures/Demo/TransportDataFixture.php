<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const TRANSPORT_CZECH_POST = 'transport_cp';
    public const TRANSPORT_PPL = 'transport_ppl';
    public const TRANSPORT_PERSONAL = 'transport_personal';
    public const TRANSPORT_OVER_LIMIT = 'transport_over_limit';

    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    private $priceConverter;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Transport\TransportDataFactory $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        TransportFacade $transportFacade,
        TransportDataFactoryInterface $transportDataFactory,
        Domain $domain,
        PriceConverter $priceConverter
    ) {
        $this->transportFacade = $transportFacade;
        $this->transportDataFactory = $transportDataFactory;
        $this->domain = $domain;
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 5;
        $transportData->deliveryCode = 'A';
        $transportData->typeOfDeliveryKey = 1;
        $transportData->maxWeight = 5000;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Czech post', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('99.95'));
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 4;
        $transportData->deliveryCode = 'B';
        $transportData->typeOfDeliveryKey = 2;
        $transportData->trackingUrl = 'https://www.ppl.cz/vyhledat-zasilku?shipmentId={tracking_number}';

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('PPL', [], 'dataFixtures', $locale);
            $transportData->trackingInstructions[$locale] = t('To track your package, click on this link: <a href="{tracking_url}">{tracking_url}</a>.', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;
        $transportData->deliveryCode = 'C';
        $transportData->typeOfDeliveryKey = 3;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Personal collection', [], 'dataFixtures', $locale);
            $transportData->description[$locale] = t('You will be welcomed by friendly staff!', [], 'dataFixtures', $locale);
            $transportData->instructions[$locale] = t('We are looking forward to your visit.', [], 'dataFixtures', $locale);
        }

        $transportData->personalPickup = true;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;
        $transportData->deliveryCode = 'E';
        $transportData->typeOfDeliveryKey = 5;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Nadlimitní', [], 'dataFixtures', $locale);
            $transportData->description[$locale] = t('Vhodné pro nadměrné zboží', [], 'dataFixtures', $locale);
            $transportData->instructions[$locale] = t('Očekávejte dodávku koncem příštího měsíce', [], 'dataFixtures', $locale);
        }

        $transportData->personalPickup = false;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_OVER_LIMIT, $transportData);
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Transport\TransportData $transportData
     */
    private function createTransport($referenceName, TransportData $transportData)
    {
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    private function setPriceForAllDomains(TransportData $transportData, Money $price): void
    {
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domain->getId());
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
            $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                $price,
                $currencyCzk,
                $vat->getPercent(),
                $domain->getId()
            );

            $transportData->vatsIndexedByDomainId[$domain->getId()] = $vat;
            $transportData->pricesIndexedByDomainId[$domain->getId()] = $convertedPrice;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            VatDataFixture::class,
            CurrencyDataFixture::class,
            SettingValueDataFixture::class,
        ];
    }
}
