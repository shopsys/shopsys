<?php

namespace Tests\ShopBundle\Database\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory;
use Tests\ShopBundle\Test\DatabaseTestCase;

class IndependentTransportVisibilityCalculationTest extends DatabaseTestCase
{
    const FIRST_DOMAIN_ID = 1;
    const SECOND_DOMAIN_ID = 2;

    public function testIsIndependentlyVisible(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            1 => true,
            2 => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, false);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $independentTransportVisibilityCalculation =
            $this->getContainer()->get(IndependentTransportVisibilityCalculation::class);
        /* @var $independentTransportVisibilityCalculation \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation */

        $this->assertTrue($independentTransportVisibilityCalculation->isIndependentlyVisible($transport, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $transportData = $this->getTransportDataFactory()->create();
        $transportData->name = [
            'cs' => null,
            'en' => null,
        ];
        $transportData->vat = $vat;
        $transportData->hidden = false;
        $transportData->enabled = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => false,
        ];

        $transport = new Transport($transportData);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $independentTransportVisibilityCalculation =
            $this->getContainer()->get(IndependentTransportVisibilityCalculation::class);
        /* @var $independentTransportVisibilityCalculation \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation */

        $this->assertFalse($independentTransportVisibilityCalculation->isIndependentlyVisible($transport, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, false);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $independentTransportVisibilityCalculation =
            $this->getContainer()->get(IndependentTransportVisibilityCalculation::class);
        /* @var $independentTransportVisibilityCalculation \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation */

        $this->assertFalse($independentTransportVisibilityCalculation->isIndependentlyVisible($transport, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => false,
        ];

        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, true);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $independentTransportVisibilityCalculation =
            $this->getContainer()->get(IndependentTransportVisibilityCalculation::class);
        /* @var $independentTransportVisibilityCalculation \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation */

        $this->assertFalse($independentTransportVisibilityCalculation->isIndependentlyVisible($transport, self::FIRST_DOMAIN_ID));
    }

    public function getDefaultTransport(Vat $vat, array $enabledForDomains, bool $hidden): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        $transportDataFactory = $this->getTransportDataFactory();

        $transportData = $transportDataFactory->create();
        $transportData->name = [
            'cs' => 'paymentName',
            'en' => 'paymentName',
        ];

        $transportData->vat = $vat;
        $transportData->hidden = $hidden;
        $transportData->enabled = $enabledForDomains;

        return new Transport($transportData);
    }

    private function getDefaultVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        return new Vat($vatData);
    }

    public function getTransportDataFactory(): \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory
    {
        return $this->getContainer()->get(TransportDataFactory::class);
    }
}
