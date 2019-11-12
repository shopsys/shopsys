<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Transport\Transport;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class IndependentTransportVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     * @inject
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation
     * @inject
     */
    private $independentTransportVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     * @inject
     */
    private $transportDataFactory;

    public function testIsIndependentlyVisible()
    {
        $em = $this->getEntityManager();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, false);

        $em->persist($transport);
        $em->flush();

        $this->assertTrue($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        $em = $this->getEntityManager();

        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $transportData->name = $names;
        $transportData->hidden = false;
        $transportData->enabled = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = new Transport($transportData);

        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $em = $this->getEntityManager();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => false,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, false);

        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $em = $this->getEntityManager();

        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, true);

        $em->persist($transport);
        $em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    /**
     * @param array $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Transport\Transport
     */
    public function getDefaultTransport($enabledForDomains, $hidden)
    {
        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'transportName';
        }
        $transportData->name = $names;

        $transportData->hidden = $hidden;
        $transportData->enabled = $enabledForDomains;

        return new Transport($transportData);
    }
}
