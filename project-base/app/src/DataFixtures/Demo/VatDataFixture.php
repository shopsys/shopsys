<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class VatDataFixture extends AbstractReferenceFixture
{
    public const VAT_ZERO = 'vat_zero';
    public const VAT_SECOND_LOW = 'vat_second_low';
    public const VAT_LOW = 'vat_low';
    public const VAT_HIGH = 'vat_high';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory $vatDataFactory
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly VatFacade $vatFacade,
        private readonly VatDataFactoryInterface $vatDataFactory,
        private readonly Setting $setting,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        /**
         * Vat with zero rate is created in database migration.
         *
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135343
         */
        $vatZeroRate = $this->vatFacade->getById(1);
        $this->addReferenceForDomain(self::VAT_ZERO, $vatZeroRate, Domain::FIRST_DOMAIN_ID);

        $vatData = $this->vatDataFactory->create();

        foreach ($this->domain->getAll() as $domainConfig) {
            if ($domainConfig->getId() !== Domain::FIRST_DOMAIN_ID) {
                $vatData->name = t('Zero rate', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
                $vatData->percent = '0';
                $this->createVat($vatData, $domainConfig->getId(), self::VAT_ZERO);
            }

            $vatData->name = t('Second reduced rate', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $vatData->percent = '10';
            $this->createVat($vatData, $domainConfig->getId(), self::VAT_SECOND_LOW);

            $vatData->name = t('Reduced rate', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $vatData->percent = '15';
            $this->createVat($vatData, $domainConfig->getId(), self::VAT_LOW);

            $vatData->name = t('Standard rate', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $vatData->percent = '21';
            $this->createVat($vatData, $domainConfig->getId(), self::VAT_HIGH);

            $this->setHighVatAsDefault($domainConfig->getId());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @param int $domainId
     * @param string|null $referenceName
     */
    private function createVat(VatData $vatData, int $domainId, $referenceName = null)
    {
        $vat = $this->vatFacade->create($vatData, $domainId);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $vat, $domainId);
        }
    }

    /**
     * @param int $domainId
     */
    private function setHighVatAsDefault(int $domainId): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $defaultVat */
        $defaultVat = $this->getReferenceForDomain(self::VAT_HIGH, $domainId);
        $this->setting->setForDomain(Vat::SETTING_DEFAULT_VAT, $defaultVat->getId(), $domainId);
    }
}
