<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const TRANSPORT_CZECH_POST = 'transport_cp';
    public const TRANSPORT_PPL = 'transport_ppl';
    public const TRANSPORT_PERSONAL = 'transport_personal';

    /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
    protected $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     */
    protected $transportDataFactory;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations
     */
    protected $dataFixturesTranslations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface $transportDataFactory
     * @param \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations $dataFixturesTranslations
     */
    public function __construct(
        TransportFacade $transportFacade,
        TransportDataFactoryInterface $transportDataFactory,
        DataFixturesTranslations $dataFixturesTranslations
    ) {
        $this->transportFacade = $transportFacade;
        $this->transportDataFactory = $transportDataFactory;
        $this->dataFixturesTranslations = $dataFixturesTranslations;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::TRANSPORT_CZECH_POST
        );
        $transportData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => Money::create('99.95'),
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => Money::create('3.95'),
        ];
        $transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::TRANSPORT_PPL
        );
        $transportData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => Money::create('199.95'),
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => Money::create('6.95'),
        ];
        $transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $this->createTransport(self::TRANSPORT_PPL, $transportData);

        $transportData = $this->transportDataFactory->create();
        $transportData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::TRANSPORT_PERSONAL
        );
        $transportData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => Money::zero(),
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => Money::zero(),
        ];
        $transportData->description = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
            self::TRANSPORT_PERSONAL
        );
        $transportData->instructions = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_INSTRUCTIONS,
            self::TRANSPORT_PERSONAL
        );
        $transportData->vat = $this->getReference(VatDataFixture::VAT_ZERO);
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);
    }

    /**
     * @param string $referenceName
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function createTransport($referenceName, TransportData $transportData)
    {
        $transport = $this->transportFacade->create($transportData);
        $this->addReference($referenceName, $transport);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            VatDataFixture::class,
            CurrencyDataFixture::class,
        ];
    }
}
