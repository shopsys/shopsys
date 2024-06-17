<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Transport\TransportDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportType;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '5e4cf5fd-16f1-4f1e-8a1b-fe81286ce8ed';

    public const TRANSPORT_CZECH_POST = 'transport_cp';
    public const TRANSPORT_PPL = 'transport_ppl';
    public const TRANSPORT_PERSONAL = 'transport_personal';
    public const TRANSPORT_DRONE = 'transport_drone';
    public const TRANSPORT_PACKETERY = 'transport_packetery';

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Transport\TransportDataFactory $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly TransportDataFactory $transportDataFactory,
        private readonly Domain $domain,
        private readonly PriceConverter $priceConverter,
        private readonly TransportTypeFacade $transportTypeFacade,
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

        $transportData->transportType = $this->transportTypeFacade->getByCode(TransportType::TYPE_PERSONAL_PICKUP);

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Suitable for all kinds of goods', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('Expect delivery by the end of next month', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_DRONE, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 2;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Packeta delivery company', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('Probably best value for your money', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('49.95'));
        $transportData->transportType = $this->transportTypeFacade->getByCode(TransportType::TYPE_PACKETERY);
        $this->createTransport(self::TRANSPORT_PACKETERY, $transportData);
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Transport\TransportData $transportData
     */
    private function createTransport(string $referenceName, TransportData $transportData)
    {
        $transportData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    private function setPriceForAllDomains(TransportData $transportData, Money $price): void
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domain->getId(), Vat::class);

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
