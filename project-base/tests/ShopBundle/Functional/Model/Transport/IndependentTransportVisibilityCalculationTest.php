<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\ShopBundle\Model\Transport\Transport;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class IndependentTransportVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    protected function setUp()
    {
        $this->localization = $this->getContainer()->get(Localization::class);
        parent::setUp();
    }

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation
     * @inject
     */
    private $independentTransportVisibilityCalculation;

    /**
     * @var \Shopsys\ShopBundle\Model\Transport\TransportDataFactory
     * @inject
     */
    private $transportDataFactory;

    public function testIsIndependentlyVisible()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, false);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $this->assertTrue($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $transportData->name = $names;
        $transportData->vat = $vat;
        $transportData->hidden = false;
        $transportData->enabled = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = new Transport($transportData);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => false,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, false);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, true);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param array $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Transport\Transport
     */
    public function getDefaultTransport(Vat $vat, $enabledForDomains, $hidden)
    {
        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'transportName';
        }
        $transportData->name = $names;

        $transportData->vat = $vat;
        $transportData->hidden = $hidden;
        $transportData->enabled = $enabledForDomains;

        return new Transport($transportData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    private function getDefaultVat()
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        return new Vat($vatData);
    }
}
