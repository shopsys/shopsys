<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class IndependentTransportVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

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

    public function testIsIndependentlyVisible(): void
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
                Domain::FIRST_DOMAIN_ID
            )
        );
    }

    public function testIsIndependentlyVisibleEmptyName(): void
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
                Domain::FIRST_DOMAIN_ID
            )
        );
    }

    public function testIsIndependentlyVisibleNotOnDomain(): void
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
                Domain::FIRST_DOMAIN_ID
            )
        );
    }

    public function testIsIndependentlyVisibleHidden(): void
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
                Domain::FIRST_DOMAIN_ID
            )
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
                Domain::FIRST_DOMAIN_ID
            )
        );
    }

    /**
     * @param array $enabledForDomains
     * @param bool $hidden
     * @param bool $deleted
     * @return \App\Model\Transport\Transport
     */
    public function getDefaultTransport(array $enabledForDomains, bool $hidden, bool $deleted = false): \App\Model\Transport\Transport
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
