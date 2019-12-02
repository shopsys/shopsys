<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface
     */
    protected $vatDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface $vatDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        VatFacade $vatFacade,
        VatDataFactoryInterface $vatDataFactory,
        Setting $setting,
        Domain $domain
    ) {
        $this->vatFacade = $vatFacade;
        $this->vatDataFactory = $vatDataFactory;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        /**
         * Vat with zero rate is created in database migration.
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135343
         */
        $vatZeroRate = $this->vatFacade->getById(1);
        $this->addReferenceForDomain(self::VAT_ZERO, $vatZeroRate, Domain::FIRST_DOMAIN_ID);

        $vatData = $this->vatDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId !== 1) {
                $vatData->name = 'Zero rate';
                $vatData->percent = '0';
                $this->createVat($vatData, $domainId, self::VAT_ZERO);
            }

            $vatData->name = 'Second reduced rate';
            $vatData->percent = '10';
            $this->createVat($vatData, $domainId, self::VAT_SECOND_LOW);

            $vatData->name = 'Reduced rate';
            $vatData->percent = '15';
            $this->createVat($vatData, $domainId, self::VAT_LOW);

            $vatData->name = 'Standard rate';
            $vatData->percent = '21';
            $this->createVat($vatData, $domainId, self::VAT_HIGH);

            $this->setHighVatAsDefault($domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @param int $domainId
     * @param string|null $referenceName
     */
    protected function createVat(VatData $vatData, int $domainId, $referenceName = null)
    {
        $vat = $this->vatFacade->create($vatData, $domainId);
        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $vat, $domainId);
        }
    }

    /**
     * @param int $domainId
     */
    protected function setHighVatAsDefault(int $domainId): void
    {
        $defaultVat = $this->getReferenceForDomain(self::VAT_HIGH, $domainId);
        /** @var $defaultVat \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat */
        $this->setting->setForDomain(Vat::SETTING_DEFAULT_VAT, $defaultVat->getId(), $domainId);
    }
}
