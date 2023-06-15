<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;

class IndependentTransportVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private Localization $localization;

    /**
     * @inject
     */
    private IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation;

    /**
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

        $this->assertTrue(
            $this->independentTransportVisibilityCalculation->isIndependentlyVisible(
                $transport,
                Domain::FIRST_DOMAIN_ID,
            ),
        );
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
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

        $transport = new Transport($transportData);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertFalse(
            $this->independentTransportVisibilityCalculation->isIndependentlyVisible(
                $transport,
                Domain::FIRST_DOMAIN_ID,
            ),
        );
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

        $this->assertFalse(
            $this->independentTransportVisibilityCalculation->isIndependentlyVisible(
                $transport,
                Domain::FIRST_DOMAIN_ID,
            ),
        );
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

        $this->assertFalse(
            $this->independentTransportVisibilityCalculation->isIndependentlyVisible(
                $transport,
                Domain::FIRST_DOMAIN_ID,
            ),
        );
    }

    public function testIsNotIndependentlyVisibleWhenDeleted(): void
    {
        $enabledOnDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => true,
        ];

        $transport = $this->getDefaultTransport($enabledOnDomains, false, true);

        $this->em->persist($transport);
        $this->em->flush();

        $this->assertFalse(
            $this->independentTransportVisibilityCalculation->isIndependentlyVisible(
                $transport,
                Domain::FIRST_DOMAIN_ID,
            ),
        );
    }

    /**
     * @param array $enabledForDomains
     * @param bool $hidden
     * @param bool $deleted
     * @return \App\Model\Transport\Transport
     */
    public function getDefaultTransport($enabledForDomains, $hidden, bool $deleted = false)
    {
        $transportData = $this->transportDataFactory->create();
        $names = [];

        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'transportName';
        }
        $transportData->name = $names;

        $transportData->hidden = $hidden;
        $transportData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);

        $transport = new Transport($transportData);

        if ($deleted) {
            $transport->markAsDeleted();
        }

        return $transport;
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
