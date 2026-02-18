<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing\Vat;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class VatFacadeTest extends TestCase
{
    public function testGetDefaultVat(): void
    {
        $vatData = new VatData();
        $vatData->percent = '0';
        $vatData->name = 'vat name';

        $expected = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $emStub = $this->createStub(EntityManager::class);

        $settingMock = $this->getMockBuilder(Setting::class)
            ->onlyMethods(['getForDomain', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $settingMock
            ->expects($this->once())
            ->method('getForDomain')
            ->with($this->equalTo(Vat::SETTING_DEFAULT_VAT))
            ->willReturn(1);

        $vatRepositoryMock = $this->getMockBuilder(VatRepository::class)
            ->onlyMethods(['findById', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $vatRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(1))
            ->willReturn($expected);

        $domainStub = $this->createStub(Domain::class);

        $productRecalculationDispatcherStub = $this->createStub(ProductRecalculationDispatcher::class);

        $vatFacade = new VatFacade(
            $emStub,
            $vatRepositoryMock,
            $settingMock,
            new VatFactory(new EntityNameResolver([])),
            $domainStub,
            $productRecalculationDispatcherStub,
        );

        $defaultVat = $vatFacade->getDefaultVatForDomain(Domain::FIRST_DOMAIN_ID);

        $this->assertSame($expected, $defaultVat);
    }

    public function testSetDefaultVatForFirstDomain(): void
    {
        $emStub = $this->createStub(EntityManager::class);
        $vatRepositoryStub = $this->createStub(VatRepository::class);

        $vatMock = $this->getMockBuilder(Vat::class)
            ->onlyMethods(['getId', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $vatMock->expects($this->once())->method('getId')->willReturn(1);

        $settingMock = $this->getMockBuilder(Setting::class)
            ->onlyMethods(['setForDomain', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $settingMock
            ->expects($this->once())
            ->method('setForDomain')
            ->with($this->equalTo(Vat::SETTING_DEFAULT_VAT), $this->equalTo(1));

        $domainStub = $this->createStub(Domain::class);

        $productRecalculationDispatcherStub = $this->createStub(ProductRecalculationDispatcher::class);

        $vatFacade = new VatFacade(
            $emStub,
            $vatRepositoryStub,
            $settingMock,
            new VatFactory(new EntityNameResolver([])),
            $domainStub,
            $productRecalculationDispatcherStub,
        );
        $vatFacade->setDefaultVatForDomain($vatMock, Domain::FIRST_DOMAIN_ID);
    }
}
