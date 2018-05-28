<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DataFixtures\Base\CurrencyDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Base\VatDataFixture;
use Shopsys\FrameworkBundle\Model\Transport\TransportEditData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class TransportDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    const TRANSPORT_CZECH_POST = 'transport_cp';
    const TRANSPORT_PPL = 'transport_ppl';
    const TRANSPORT_PERSONAL = 'transport_personal';

    /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
    private $transportFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     */
    public function __construct(TransportFacade $transportFacade)
    {
        $this->transportFacade = $transportFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $transportEditData = new TransportEditData();
        $transportEditData->transportData->name = [
            'cs' => 'Česká pošta - balík do ruky',
            'en' => 'Czech post',
        ];
        $transportEditData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => 99.95,
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => 3.95,
        ];
        $transportEditData->transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $transportEditData->transportData->domains = [Domain::FIRST_DOMAIN_ID];
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportEditData);

        $transportEditData = new TransportEditData();
        $transportEditData->transportData->name = [
            'cs' => 'PPL',
            'en' => 'PPL',
        ];
        $transportEditData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => 199.95,
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => 6.95,
        ];
        $transportEditData->transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $transportEditData->transportData->domains = [Domain::FIRST_DOMAIN_ID];
        $this->createTransport(self::TRANSPORT_PPL, $transportEditData);

        $transportEditData = new TransportEditData();
        $transportEditData->transportData->name = [
            'cs' => 'Osobní převzetí',
            'en' => 'Personal collection',
        ];
        $transportEditData->pricesByCurrencyId = [
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK)->getId() => 0,
            $this->getReference(CurrencyDataFixture::CURRENCY_EUR)->getId() => 0,
        ];
        $transportEditData->transportData->description = [
            'cs' => 'Uvítá Vás milý personál!',
            'en' => 'You will be welcomed by friendly staff!',
        ];
        $transportEditData->transportData->instructions = [
            'cs' => 'Těšíme se na Vaši návštěvu.',
            'en' => 'We are looking forward to your visit.',
        ];
        $transportEditData->transportData->vat = $this->getReference(VatDataFixture::VAT_ZERO);
        $transportEditData->transportData->domains = [Domain::FIRST_DOMAIN_ID];
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportEditData);
    }

    /**
     * @param string $referenceName
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportEditData $transportEditData
     */
    private function createTransport($referenceName, TransportEditData $transportEditData)
    {
        $transport = $this->transportFacade->create($transportEditData);
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
