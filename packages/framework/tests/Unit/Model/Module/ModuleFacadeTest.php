<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Module;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Module\EnabledModule;
use Shopsys\FrameworkBundle\Model\Module\EnabledModuleFactory;
use Shopsys\FrameworkBundle\Model\Module\EnabledModuleRepository;
use Shopsys\FrameworkBundle\Model\Module\Exception\UnsupportedModuleException;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;

final class ModuleFacadeTest extends TestCase
{
    public function testEnabledModuleNamesAreLoadedOnceForSeveralModules(): void
    {
        $enabledModuleRepositoryMock = $this->createMock(EnabledModuleRepository::class);
        $enabledModuleRepositoryMock->expects($this->once())
            ->method('getAllNames')
            ->willReturn([ModuleList::PRODUCT_FILTER_COUNTS]);
        $moduleFacade = $this->createModuleFacade($enabledModuleRepositoryMock);

        $this->assertTrue($moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS));
        $this->assertTrue($moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS));
        $this->assertFalse($moduleFacade->isEnabled(ModuleList::ACCESSORIES_ON_BUY));
    }

    public function testEnabledModuleNamesAreLoadedAgainAfterModuleIsEnabled(): void
    {
        $enabledModuleRepositoryMock = $this->createMock(EnabledModuleRepository::class);
        $enabledModuleRepositoryMock->expects($this->exactly(2))
            ->method('getAllNames')
            ->willReturnOnConsecutiveCalls([], [ModuleList::PRODUCT_FILTER_COUNTS]);
        $moduleFacade = $this->createModuleFacade($enabledModuleRepositoryMock);

        $this->assertFalse($moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS));

        $moduleFacade->setEnabled(ModuleList::PRODUCT_FILTER_COUNTS, true);

        $this->assertTrue($moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS));
    }

    public function testUnsupportedModuleIsNotLookedUpAmongEnabledModuleNames(): void
    {
        $enabledModuleRepositoryMock = $this->createMock(EnabledModuleRepository::class);
        $enabledModuleRepositoryMock->method('checkModuleIsSupported')
            ->willThrowException(new UnsupportedModuleException('unsupported'));
        $enabledModuleRepositoryMock->expects($this->never())->method('getAllNames');

        $this->expectException(UnsupportedModuleException::class);

        $this->createModuleFacade($enabledModuleRepositoryMock)->isEnabled('unsupported');
    }

    private function createModuleFacade(EnabledModuleRepository&MockObject $enabledModuleRepositoryMock): ModuleFacade
    {
        $enabledModuleFactoryStub = $this->createStub(EnabledModuleFactory::class);
        $enabledModuleFactoryStub->method('create')
            ->willReturnCallback(static fn (string $name): EnabledModule => new EnabledModule($name));

        return new ModuleFacade(
            $this->createStub(EntityManagerInterface::class),
            $enabledModuleRepositoryMock,
            $enabledModuleFactoryStub,
            new InMemoryCache(),
        );
    }
}
