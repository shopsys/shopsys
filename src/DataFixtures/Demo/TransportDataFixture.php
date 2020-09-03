<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportPackage\TransportPackageDataFactory;
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
    public const TRANSPORT_PALLET = 'transport_pallet';

    /** @var \App\Model\Transport\TransportFacade */
    private $transportFacade;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     */
    private $transportDataFactory;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    private $priceConverter;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageDataFactory
     */
    private TransportPackageDataFactory $transportPackageDataFactory;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Transport\TransportDataFactory $transportDataFactory
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     * @param \App\Model\Transport\TransportPackage\TransportPackageDataFactory $transportPackageDataFactory
     */
    public function __construct(
        TransportFacade $transportFacade,
        TransportDataFactoryInterface $transportDataFactory,
        Domain $domain,
        PriceConverter $priceConverter,
        TransportPackageDataFactory $transportPackageDataFactory
    ) {
        $this->transportFacade = $transportFacade;
        $this->transportDataFactory = $transportDataFactory;
        $this->domain = $domain;
        $this->priceConverter = $priceConverter;
        $this->transportPackageDataFactory = $transportPackageDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Product\Type\ProductType $productTypeCommon */
        $productTypeCommon = $this->getReference(ProductTypeDataFixture::TYPE_COMMON);
        /** @var \App\Model\Product\Type\ProductType $productTypeOversized */
        $productTypeOversized = $this->getReference(ProductTypeDataFixture::TYPE_OVERSIZED);

        $transportData = $this->transportDataFactory->create();
        $transportData->externalId = 1;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Czech post', [], 'dataFixtures', $locale);
        }

        $transportData->type = Transport::TYPE_PACKAGE;
        $transportData->productTypes = [
            $productTypeCommon,
        ];
        $this->setPriceForAllDomains($transportData, Money::create('99.95'));
        $transportPackageData = $this->transportPackageDataFactory->create();
        $transportPackageData->domainId = 1;
        $transportPackageData->priceWithVat = Money::create(99);
        $transportPackageData->maxWeight = 15;
        $transportPackageData->dimension1 = 60;
        $transportPackageData->dimension2 = 50;
        $transportPackageData->dimension3 = 40;
        $transportPackageData2 = $this->transportPackageDataFactory->create();
        $transportPackageData2->domainId = 2;
        $transportPackageData2->priceWithVat = Money::create(4);
        $transportPackageData2->maxWeight = 15;
        $transportPackageData2->dimension1 = 55;
        $transportPackageData2->dimension2 = 50;
        $transportPackageData2->dimension3 = 40;
        $transportData->transportPackages = [
            $transportPackageData,
            $transportPackageData2,
        ];
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->externalId = 2;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('PPL', [], 'dataFixtures', $locale);
        }

        $transportData->type = Transport::TYPE_PACKAGE;
        $transportData->productTypes = [
            $productTypeCommon,
            $productTypeOversized,
        ];
        $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $transportPackageData = $this->transportPackageDataFactory->create();
        $transportPackageData->domainId = 1;
        $transportPackageData->priceWithVat = Money::create(99);
        $transportPackageData->maxWeight = 20;
        $transportPackageData->dimension1 = 60;
        $transportPackageData->dimension2 = 50;
        $transportPackageData->dimension3 = 40;
        $transportPackageData2 = $this->transportPackageDataFactory->create();
        $transportPackageData2->domainId = 2;
        $transportPackageData2->priceWithVat = Money::create(4);
        $transportPackageData2->maxWeight = 15;
        $transportPackageData2->maxGirth = 350;
        $transportPackageData3 = $this->transportPackageDataFactory->create();
        $transportPackageData3->domainId = 1;
        $transportPackageData3->priceWithVat = Money::create(199);
        $transportPackageData3->maxWeight = 30;
        $transportPackageData3->dimension1 = 60;
        $transportPackageData3->dimension2 = 50;
        $transportPackageData3->dimension3 = 50;
        $transportData->transportPackages = [
            $transportPackageData,
            $transportPackageData2,
            $transportPackageData3,
        ];
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->externalId = 3;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Personal collection', [], 'dataFixtures', $locale);
            $transportData->description[$locale] = t('You will be welcomed by friendly staff!', [], 'dataFixtures', $locale);
            $transportData->instructions[$locale] = t('We are looking forward to your visit.', [], 'dataFixtures', $locale);
        }

        $transportData->type = Transport::TYPE_COMMON;
        $transportData->productTypes = [
            $productTypeCommon,
            $productTypeOversized,
        ];

        $transportData->personalPickup = true;

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->externalId = 4;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Paletová přeprava', [], 'dataFixtures', $locale);
            $transportData->description[$locale] = t('Dovezeme Vám  to až domů naším vlastním rozvozem.', [], 'dataFixtures', $locale);
            $transportData->instructions[$locale] = t('Zásilku může převzít pouze osoba starší 18-ti let.', [], 'dataFixtures', $locale);
        }

        $transportData->type = Transport::TYPE_PALLET;
        $transportData->productTypes = [
            $productTypeCommon,
            $productTypeOversized,
        ];

        $transportData->personalPickup = false;

        $this->setPriceForAllDomains($transportData, Money::create('399.95'));
        $this->createTransport(self::TRANSPORT_PALLET, $transportData);
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
            $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());

            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domain->getId());

            $transportData->vatsIndexedByDomainId[$domain->getId()] = $vat;
            $transportData->pricesIndexedByDomainId[$domain->getId()] = $price;
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
            ProductTypeDataFixture::class,
        ];
    }
}
