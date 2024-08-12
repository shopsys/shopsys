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
    public const string VAT_ZERO = 'vat_zero';
    public const string VAT_SECOND_LOW = 'vat_second_low';
    public const string VAT_LOW = 'vat_low';
    public const string VAT_HIGH = 'vat_high';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory $vatDataFactory
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(
        private readonly VatFacade $vatFacade,
        private readonly VatDataFactoryInterface $vatDataFactory,
        private readonly Setting $setting,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /**
         * Vat with zero rate is created in database migration.
         *
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135343
         */
        $vatZeroRate = $this->vatFacade->getById(1);
        $this->addReferenceForDomain(self::VAT_ZERO, $vatZeroRate, Domain::FIRST_DOMAIN_ID);

        $vatData = $this->vatDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if ($domainConfig !== $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()) {
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
     * @param string $referenceName
     */
    private function createVat(VatData $vatData, int $domainId, string $referenceName): void
    {
        $vat = $this->vatFacade->create($vatData, $domainId);

        $this->addReferenceForDomain($referenceName, $vat, $domainId);
    }

    /**
     * @param int $domainId
     */
    private function setHighVatAsDefault(int $domainId): void
    {
        $defaultVat = $this->getReferenceForDomain(self::VAT_HIGH, $domainId, Vat::class);
        $this->setting->setForDomain(Vat::SETTING_DEFAULT_VAT, $defaultVat->getId(), $domainId);
    }
}
