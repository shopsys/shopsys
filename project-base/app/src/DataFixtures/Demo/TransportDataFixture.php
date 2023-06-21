<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Transport\TransportDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const TRANSPORT_CZECH_POST = 'transport_cp';
    public const TRANSPORT_PPL = 'transport_ppl';
    public const TRANSPORT_PERSONAL = 'transport_personal';
    public const TRANSPORT_DRONE = 'transport_drone';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '5e4cf5fd-16f1-4f1e-8a1b-fe81286ce8ed',
        '45e4fe5a-db4a-49e8-80ec-5242a9858dce',
        'ca676696-7fcf-43d8-a77e-9e9892cd464a',
        'c5bf95f7-0093-4345-96d9-562e9371a273',
    ];

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Transport\TransportDataFactory $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly TransportDataFactory $transportDataFactory,
        private readonly Domain $domain,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 5;
        $transportData->maxWeight = 5000;
        $transportData->trackingUrl = 'https://www.postaonline.cz/trackandtrace/-/zasilka/cislo?parcelNumbers={tracking_number}';

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->trackingInstructions[$locale] = t('To track your package, click on this link: <a href="{tracking_url}">{tracking_number}</a>.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Czech state post service.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('the Czech Post will try to deliver your parcel on time, but it will not succeed and despite the constant presence of your person at home, it will not catch you and you will have to pick up the parcel personally at the counter. Here, however, you have to endure an endlessly long line and an eternally grumpy lady postman.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('99.95'));
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 4;
        $transportData->trackingUrl = 'https://www.ppl.cz/vyhledat-zasilku?shipmentId={tracking_number}';

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->trackingInstructions[$locale] = t('To track your package, click on this link: <a href="{tracking_url}">{tracking_url}</a>.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t(
                'You will be welcomed by friendly staff!',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $transportData->instructions[$locale] = t(
                'We are looking forward to your visit.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }

        $transportData->personalPickup = true;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Vhodné pro všechny druhy zboží', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('Očekávejte dodávku koncem příštího měsíce', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $transportData->personalPickup = false;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_DRONE, $transportData);
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Transport\TransportData $transportData
     */
    private function createTransport($referenceName, TransportData $transportData)
    {
        $transportData->uuid = array_pop($this->uuidPool);
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    private function setPriceForAllDomains(TransportData $transportData, Money $price): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domain->getId());

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                $price,
                $currencyCzk,
                $vat->getPercent(),
                $domain->getId(),
            );

            $transportData->vatsIndexedByDomainId[$domain->getId()] = $vat;
            $transportData->pricesIndexedByDomainId[$domain->getId()] = $convertedPrice;
        }
    }

    /**
     * {@inheritdoc}
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
