<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Tests\App\Test\FunctionalTestCase;

class IndependentTransportVisibilityCalculationTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     * @inject
     */
    private Localization $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation
     * @inject
     */
    private IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    public function testIsIndependentlyVisible()
    {
        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, false);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertTrue($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        /** @var \App\Model\Transport\TransportData $transportData */
        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $transportData->name = $names;
        $transportData->hidden = false;
        $transportData->enabled = $this->getFilteredEnabledForDomains([
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ]);
        $transportData->deliveryCode = 'A';
        $transportData->typeOfDeliveryKey = 1;

        $transport = new Transport($transportData);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => false,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, false);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, true);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertFalse($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, Domain::FIRST_DOMAIN_ID));
    }

    /**
     * @param array $enabledForDomains
     * @param bool $hidden
     * @return \App\Model\Transport\Transport
     */
    public function getDefaultTransport($enabledForDomains, $hidden)
    {
        /** @var \App\Model\Transport\TransportData $transportData */
        $transportData = $this->transportDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'transportName';
        }
        $transportData->name = $names;

        $transportData->hidden = $hidden;
        $transportData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);
        $transportData->deliveryCode = 'A';
        $transportData->typeOfDeliveryKey = 1;

        return new Transport($transportData);
    }

    /**
     * @param bool[] $enabledForDomains
     * @return bool[]
     */
    private function getFilteredEnabledForDomains(array $enabledForDomains): array
    {
        return array_intersect_key($enabledForDomains, array_flip($this->domain->getAllIds()));
    }
}
