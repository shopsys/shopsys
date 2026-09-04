<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '5e4cf5fd-16f1-4f1e-8a1b-fe81286ce8ed';

    public const string TRANSPORT_CZECH_POST = 'transport_cp';
    public const string TRANSPORT_PPL = 'transport_ppl';
    public const string TRANSPORT_PERSONAL = 'transport_personal';
    public const string TRANSPORT_DRONE = 'transport_drone';
    public const string TRANSPORT_PACKETERY = 'transport_packetery';
    public const string TRANSPORT_EMAIL = 'transport_email';
    public const string TRANSPORT_EMAIL_UUID = 'ecd99cd8-efca-4981-bbb0-0638d7243cef';

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly TransportDataFactory $transportDataFactory,
        private readonly PriceConverter $priceConverter,
        private readonly TransportInputPricesDataFactory $transportInputPricesDataFactory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 2;
        $transportData->deliveryDaysOfWeek = DateTimeHelper::ALL_DAYS_OF_WEEK;
        $transportData->deliversOnPublicHolidays = true;
        $transportData->deliversOnInternalClosedDays = true;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $transportData->enabled[$domainConfig->getId()] = true;
            $transportData->name[$locale] = t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Packeta delivery company', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('Probably best value for your money', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('49.95'));
        $transportData->type = TransportTypeEnum::TYPE_PACKETERY;
        $transportData->group = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_PICKUP_POINT, TransportGroup::class);
        $this->createTransport(self::TRANSPORT_PACKETERY, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 4;
        $transportData->trackingUrl = 'https://www.ppl.cz/vyhledat-zasilku?shipmentId={tracking_number}';

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $transportData->enabled[$domainConfig->getId()] = true;
            $transportData->name[$locale] = t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->trackingInstructions[$locale] = t('To track your package, click on this link: <a href="{tracking_url}" tabindex="0">{tracking_url}</a>.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $transportData->group = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS, TransportGroup::class);
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 5;
        $transportData->trackingUrl = 'https://www.postaonline.cz/trackandtrace/-/zasilka/cislo?parcelNumbers={tracking_number}';

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $transportData->enabled[$domainConfig->getId()] = true;
            $transportData->name[$locale] = t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->trackingInstructions[$locale] = t('To track your package, click on this link: <a href="{tracking_url}" tabindex="0">{tracking_number}</a>.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Czech state post service.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('the Czech Post will try to deliver your parcel on time, but it will not succeed and despite the constant presence of your person at home, it will not catch you and you will have to pick up the parcel personally at the counter. Here, however, you have to endure an endlessly long line and an eternally grumpy lady postman.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('99.95'), 5000);
        $transportData->group = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS, TransportGroup::class);
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $transportData->enabled[$domainConfig->getId()] = true;
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

        $transportData->type = TransportTypeEnum::TYPE_PERSONAL_PICKUP;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $transportData->group = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_PICKUP_POINT, TransportGroup::class);
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->daysUntilDelivery = 0;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $transportData->enabled[$domainConfig->getId()] = true;
            $transportData->name[$locale] = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->description[$locale] = t('Suitable for all kinds of goods', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $transportData->instructions[$locale] = t('Expect delivery by the end of next month', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::zero());
        $transportData->group = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS, TransportGroup::class);
        $this->createTransport(self::TRANSPORT_DRONE, $transportData);

        $this->addReferenceToEmailTransportCreatedByMigration();
    }

    private function addReferenceToEmailTransportCreatedByMigration(): void
    {
        $emailTransport = $this->transportFacade->findEmailTransport();

        $this->setDeterministicUuidToEmailTransport($emailTransport);
        $this->moveEmailTransportBehindTransportsCreatedByThisFixture($emailTransport);

        $this->addReference(self::TRANSPORT_EMAIL, $emailTransport);
    }

    private function setDeterministicUuidToEmailTransport(Transport $emailTransport): void
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE transports SET uuid = :uuid WHERE id = :id',
            [
                'uuid' => self::TRANSPORT_EMAIL_UUID,
                'id' => $emailTransport->getId(),
            ],
        );

        $this->em->refresh($emailTransport);
    }

    private function moveEmailTransportBehindTransportsCreatedByThisFixture(Transport $emailTransport): void
    {
        $emailTransport->setPosition(Transport::GEDMO_SORTABLE_LAST_POSITION);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    private function createTransport(string $referenceName, TransportData $transportData): void
    {
        $transportData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    private function setPriceForAllDomains(TransportData $transportData, Money $price, ?int $maxWeight = null): void
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceInDomainDefaultCurrency(
                $price,
                $currencyCzk,
                $vat->getPercent(),
                $domainId,
            );

            $transportInputPricesData = $this->transportInputPricesDataFactory->create($domainId);
            $transportInputPricesData->vat = $vat;
            $priceWithLimitData = $this->transportInputPricesDataFactory->createPriceWithLimitDataInstance();
            $priceWithLimitData->price = $convertedPrice;
            $priceWithLimitData->maxWeight = $maxWeight;
            $transportInputPricesData->pricesWithLimits = [$priceWithLimitData];

            if ($maxWeight !== null) {
                $priceWithLimitData2 = $this->transportInputPricesDataFactory->createPriceWithLimitDataInstance();
                $priceWithLimitData2->price = $convertedPrice->multiply(2);
                $priceWithLimitData2->maxWeight = $maxWeight * 2;
                $transportInputPricesData->pricesWithLimits[] = $priceWithLimitData2;
            }

            $transportData->inputPricesByDomain[$domainId] = $transportInputPricesData;
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            VatDataFixture::class,
            CurrencyDataFixture::class,
            SettingValueDataFixture::class,
            TransportGroupDataFixture::class,
        ];
    }
}
