<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport;

use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class IndependentTransportVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

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
        $transportData->enabled = $this->getFilteredEnabledForDomains([
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ]);

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
     * @return \App\Model\Transport\Transport
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
        $transportData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);

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
