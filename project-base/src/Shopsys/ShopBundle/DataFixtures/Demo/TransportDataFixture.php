<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const TRANSPORT_CZECH_POST = 'transport_cp';
    public const TRANSPORT_PPL = 'transport_ppl';
    public const TRANSPORT_PERSONAL = 'transport_personal';

    /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
    protected $transportFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Transport\TransportDataFactory
     */
    protected $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    protected $priceConverter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\ShopBundle\Model\Transport\TransportDataFactory $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        TransportFacade $transportFacade,
        TransportDataFactoryInterface $transportDataFactory,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        PriceConverter $priceConverter
    ) {
        $this->transportFacade = $transportFacade;
        $this->transportDataFactory = $transportDataFactory;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $transportData = $this->transportDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Czech post', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('99.95'));
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('PPL', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = t('Personal collection', [], 'dataFixtures', $locale);
            $transportData->description[$locale] = t('You will be welcomed by friendly staff!', [], 'dataFixtures', $locale);
            $transportData->instructions[$locale] = t('We are looking forward to your visit.', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);
    }

    /**
     * @param string $referenceName
     * @param \Shopsys\ShopBundle\Model\Transport\TransportData $transportData
     */
    protected function createTransport($referenceName, TransportData $transportData)
    {
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Transport\TransportData $transportData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    protected function setPriceForAllDomains(TransportData $transportData, Money $price): void
    {
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());

            $transportData->vatsIndexedByDomainId[$domain->getId()] = $this->getReference(sprintf('%s_%s', VatDataFixture::VAT_HIGH, $domain->getId()));
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
        ];
    }
}
