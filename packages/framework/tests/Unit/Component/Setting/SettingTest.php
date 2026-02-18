<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Setting;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Setting\SettingValue;
use Shopsys\FrameworkBundle\Component\Setting\SettingValueRepository;

class SettingTest extends TestCase
{
    public function testSet(): void
    {
        $settingValueArray = [
            [SettingValue::DOMAIN_ID_COMMON, []],
            [1, [new SettingValue('key', 'value', 1)]],
        ];

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flush', 'persist'])
            ->getMock();
        $entityManagerMock->expects($this->atLeastOnce())->method('flush');
        $entityManagerMock->expects($this->never())->method('persist');

        $settingValueRepositoryMock = $this->getMockBuilder(SettingValueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByDomainId'])
            ->getMock();
        $settingValueRepositoryMock->expects($this->atLeastOnce())->method('getAllByDomainId')->willReturnMap(
            $settingValueArray,
        );

        $setting = new Setting($entityManagerMock, $settingValueRepositoryMock);
        $this->assertSame('value', $setting->getForDomain('key', 1));
        $setting->setForDomain('key', 'newValue', 1);
        $this->assertSame('newValue', $setting->getForDomain('key', 1));

        $this->expectException(SettingValueNotFoundException::class);
        $setting->setForDomain('key2', 'value', 1);
    }

    public function testSetNotFoundException(): void
    {
        $settingValueArray = [
            [SettingValue::DOMAIN_ID_COMMON, []],
            [1, [new SettingValue('key', 'value', 1)]],
        ];

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flush', 'persist'])
            ->getMock();
        $entityManagerMock->expects($this->never())->method('flush');
        $entityManagerMock->expects($this->never())->method('persist');

        $settingValueRepositoryMock = $this->getMockBuilder(SettingValueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByDomainId'])
            ->getMock();
        $settingValueRepositoryMock->expects($this->atLeastOnce())->method('getAllByDomainId')->willReturnMap(
            $settingValueArray,
        );

        $setting = new Setting($entityManagerMock, $settingValueRepositoryMock);

        $this->expectException(SettingValueNotFoundException::class);
        $setting->setForDomain('key2', 'value', 1);
    }

    public function testGetNotFoundException(): void
    {
        $settingValueArray = [new SettingValue('key', 'value', 1)];

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flush', 'persist'])
            ->getMock();
        $entityManagerMock->expects($this->never())->method('flush');
        $entityManagerMock->expects($this->never())->method('persist');

        $settingValueRepositoryMock = $this->getMockBuilder(SettingValueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByDomainId'])
            ->getMock();
        $settingValueRepositoryMock->expects($this->atLeastOnce())->method('getAllByDomainId')->willReturn(
            $settingValueArray,
        );

        $setting = new Setting($entityManagerMock, $settingValueRepositoryMock);

        $this->expectException(SettingValueNotFoundException::class);
        $setting->getForDomain('key2', 1);
    }

    public function testGetValues(): void
    {
        $settingValueArrayByDomainIdMap = [
            [SettingValue::DOMAIN_ID_COMMON, [new SettingValue('key', 'valueCommon', SettingValue::DOMAIN_ID_COMMON)]],
            [1, [new SettingValue('key', 'value', 1)]],
            [2, []],
        ];

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flush', 'persist'])
            ->getMock();
        $entityManagerMock->expects($this->atLeastOnce())->method('flush');
        $entityManagerMock->expects($this->never())->method('persist');

        $settingValueRepositoryMock = $this->getMockBuilder(SettingValueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByDomainId'])
            ->getMock();
        $settingValueRepositoryMock->expects($this->atLeastOnce())
            ->method('getAllByDomainId')->willReturnMap($settingValueArrayByDomainIdMap);

        $setting = new Setting($entityManagerMock, $settingValueRepositoryMock);
        $this->assertSame('valueCommon', $setting->get('key'));
        $this->assertSame('value', $setting->getForDomain('key', 1));
        $setting->setForDomain('key', 'newValue', 1);
        $this->assertSame('newValue', $setting->getForDomain('key', 1));
        $setting->set('key', 'newValueCommon');
        $this->assertSame('newValue', $setting->getForDomain('key', 1));
        $this->assertSame('newValueCommon', $setting->get('key'));
    }

    public function testSetValueNewDomain(): void
    {
        $settingValueArrayByDomainIdMap = [
            [SettingValue::DOMAIN_ID_COMMON, [new SettingValue('key', 'valueCommon', SettingValue::DOMAIN_ID_COMMON)]],
            [1, [new SettingValue('key', 'value', 1)]],
            [2, []],
            [3, []],
        ];

        $entityManagerStub = $this->createDummyEntityManagerStub();

        $settingValueRepositoryMock = $this->getMockBuilder(SettingValueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByDomainId'])
            ->getMock();
        $settingValueRepositoryMock->expects($this->atLeastOnce())
            ->method('getAllByDomainId')->willReturnMap($settingValueArrayByDomainIdMap);

        $setting = new Setting($entityManagerStub, $settingValueRepositoryMock);

        $this->assertSame('value', $setting->getForDomain('key', 1));
    }

    public function testCannotSetNonexistentCommonValue(): void
    {
        $entityManagerStub = $this->createDummyEntityManagerStub();

        $settingValueRepositoryStub = $this->createStub(SettingValueRepository::class);
        $settingValueRepositoryStub->method('getAllByDomainId')->willReturn([]);

        $setting = new Setting($entityManagerStub, $settingValueRepositoryStub);

        $this->expectException(SettingValueNotFoundException::class);
        $setting->set('nonexistentKey', 'anyValue');
    }

    public function testCannotSetNonexistentValueForDomain(): void
    {
        $entityManagerStub = $this->createDummyEntityManagerStub();

        $settingValueRepositoryStub = $this->createStub(SettingValueRepository::class);
        $settingValueRepositoryStub->method('getAllByDomainId')->willReturn([]);

        $setting = new Setting($entityManagerStub, $settingValueRepositoryStub);

        $this->expectException(SettingValueNotFoundException::class);
        $setting->setForDomain('nonexistentKey', 'anyValue', 1);
    }

    private function createDummyEntityManagerStub(): EntityManager
    {
        return $this->createStub(EntityManager::class);
    }
}
