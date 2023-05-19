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
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class VatFacadeTest extends TestCase
{
    public function testGetDefaultVat()
    {
        $vatData = new VatData();
        $vatData->percent = '0';
        $vatData->name = 'vat name';

        $expected = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $emMock = $this->createMock(EntityManager::class);

        $settingMock = $this->getMockBuilder(Setting::class)
            ->setMethods(['getForDomain', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $settingMock
            ->expects($this->once())
            ->method('getForDomain')
            ->with($this->equalTo(Vat::SETTING_DEFAULT_VAT))
            ->willReturn(1);

        $vatRepositoryMock = $this->getMockBuilder(VatRepository::class)
            ->setMethods(['findById', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $vatRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(1))
            ->willReturn($expected);

        $productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $domainMock = $this->getMockBuilder(Domain::class)
            ->setMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $vatFacade = new VatFacade(
            $emMock,
            $vatRepositoryMock,
            $settingMock,
            $productPriceRecalculationSchedulerMock,
            new VatFactory(new EntityNameResolver([])),
            $domainMock,
        );

        $defaultVat = $vatFacade->getDefaultVatForDomain(Domain::FIRST_DOMAIN_ID);

        $this->assertSame($expected, $defaultVat);
    }

    public function testSetDefaultVatForFirstDomain()
    {
        $emMock = $this->createMock(EntityManager::class);
        $vatRepositoryMock = $this->createMock(VatRepository::class);

        $vatMock = $this->getMockBuilder(Vat::class)
            ->setMethods(['getId', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $vatMock->expects($this->once())->method('getId')->willReturn(1);

        $settingMock = $this->getMockBuilder(Setting::class)
            ->setMethods(['setForDomain', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $settingMock
            ->expects($this->once())
            ->method('setForDomain')
            ->with($this->equalTo(Vat::SETTING_DEFAULT_VAT), $this->equalTo(1));

        $productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock = $this->getMockBuilder(Domain::class)
            ->setMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $vatFacade = new VatFacade(
            $emMock,
            $vatRepositoryMock,
            $settingMock,
            $productPriceRecalculationSchedulerMock,
            new VatFactory(new EntityNameResolver([])),
            $domainMock,
        );
        $vatFacade->setDefaultVatForDomain($vatMock, Domain::FIRST_DOMAIN_ID);
    }
}
