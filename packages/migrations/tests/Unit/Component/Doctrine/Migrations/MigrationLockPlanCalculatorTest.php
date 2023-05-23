<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigrationsSet;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Doctrine\Migrations\Metadata\ExecutedMigrationsList;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Version\Version;
use Iterator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\MethodIsNotAllowedException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\PartialMigrationNotAllowedException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockComparator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockRepository;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000001;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000002;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000003;

class MigrationLockPlanCalculatorTest extends AbstractMigrationTestCase
{
    /**
     * @dataProvider getMigrationsDataProvider
     * @param string[] $availableMigrationClasses
     * @param string[] $orderedMigrationClassesFromLock
     * @param string[] $expectedMigrationClasses
     */
    public function testGetMigrations(
        array $availableMigrationClasses,
        array $orderedMigrationClassesFromLock,
        array $expectedMigrationClasses,
    ): void {
        $migrationLockPlanCalculator = $this->getMigrationLockPlanCalculator($availableMigrationClasses, $orderedMigrationClassesFromLock);
        $actualMigrations = $migrationLockPlanCalculator->getMigrations()->getItems();

        $this->assertSame(count($expectedMigrationClasses), count($actualMigrations));

        foreach ($actualMigrations as $key => $migration) {
            $this->assertSame($expectedMigrationClasses[$key], (string)$migration->getVersion());
        }
    }

    public function testGetPlanForVersionsThrowsException(): void
    {
        $migrationLockPlanCalculator = $this->getMigrationLockPlanCalculator([], []);
        $this->expectException(MethodIsNotAllowedException::class);
        $migrationLockPlanCalculator->getPlanForVersions([], 'direction');
    }

    public function testGetPlanUntilVersionThrowsExceptionWhenToVersionIsLowerThanAnyOfAlreadyInstalledMigrations(): void
    {
        $availableMigrationClasses = [
            Version20180101000001::class,
            Version20180101000002::class,
            Version20180101000003::class,
        ];
        $orderedMigrationClassesFromLock = $availableMigrationClasses;
        $migrationLockPlanCalculator = $this->getMigrationLockPlanCalculator($availableMigrationClasses, $orderedMigrationClassesFromLock);

        $this->expectException(PartialMigrationNotAllowedException::class);

        $toVersion = $this->createMockedAvailableMigration(Version20180101000002::class)->getVersion();
        $migrationLockPlanCalculator->getPlanUntilVersion($toVersion);
    }

    /**
     * @dataProvider getPlanUntilVersionDataProvider
     * @param string[] $availableMigrationClasses
     * @param string[] $orderedMigrationClassesFromLock
     * @param string[] $executedMigrationClasses
     * @param \Doctrine\Migrations\Version\Version $toVersion
     * @param string[] $expectedMigrationClasses
     */
    public function testGetPlanUntilVersion(
        array $availableMigrationClasses,
        array $orderedMigrationClassesFromLock,
        array $executedMigrationClasses,
        Version $toVersion,
        array $expectedMigrationClasses,
    ): void {
        $migrationLockPlanCalculator = $this->getMigrationLockPlanCalculator($availableMigrationClasses, $orderedMigrationClassesFromLock, $executedMigrationClasses);

        $actualMigrations = $migrationLockPlanCalculator->getPlanUntilVersion($toVersion);

        $this->assertSame(count($expectedMigrationClasses), count($actualMigrations));

        foreach ($actualMigrations->getItems() as $key => $migrationPlan) {
            $this->assertSame($expectedMigrationClasses[$key], (string)$migrationPlan->getVersion());
        }
    }

    /**
     * @return \Iterator
     */
    public function getMigrationsDataProvider(): Iterator
    {
        $versionClass1 = Version20180101000001::class;
        $versionClass2 = Version20180101000002::class;
        $versionClass3 = Version20180101000003::class;

        yield [
            'availableMigrationClasses' => [$versionClass1, $versionClass2, $versionClass3],
            'orderedMigrationClassesFromLock' => [$versionClass2, $versionClass3, $versionClass1],
            'expectedMigrationClasses' => [$versionClass2, $versionClass3, $versionClass1],
        ];

        yield [
            'availableMigrationsClasses' => [$versionClass2, $versionClass3, $versionClass1],
            'orderedMigrationClassesFromLock' => [],
            'expectedMigrationClasses' => [$versionClass1, $versionClass2, $versionClass3],
        ];

        yield [
            'availableMigrationsClasses' => [$versionClass1, $versionClass2, $versionClass3],
            'orderedMigrationClassesFromLock' => [$versionClass2, $versionClass3],
            'expectedMigrationClasses' => [$versionClass2, $versionClass3, $versionClass1],
        ];
    }

    /**
     * @return \Iterator
     */
    public function getPlanUntilVersionDataProvider(): Iterator
    {
        $versionClass1 = Version20180101000001::class;
        $versionClass2 = Version20180101000002::class;
        $versionClass3 = Version20180101000003::class;

        yield [
            'availableMigrationClasses' => [$versionClass1, $versionClass2],
            'orderedMigrationClassesFromLock' => [$versionClass1, $versionClass2],
            'executedMigrationClasses' => [],
            'toVersion' => $this->createMockedAvailableMigration($versionClass2)->getVersion(),
            'expectedMigrationClasses' => [$versionClass1, $versionClass2],
        ];

        yield [
            'availableMigrationClasses' => [$versionClass1, $versionClass2],
            'orderedMigrationClassesFromLock' => [$versionClass1, $versionClass2],
            'executedMigrationClasses' => [$versionClass1, $versionClass2],
            'toVersion' => $this->createMockedAvailableMigration($versionClass2)->getVersion(),
            'expectedMigrationClasses' => [],
        ];

        yield [
            'availableMigrationClasses' => [$versionClass1, $versionClass2, $versionClass3],
            'orderedMigrationClassesFromLock' => [$versionClass1, $versionClass2, $versionClass3],
            'executedMigrationClasses' => [$versionClass1, $versionClass2],
            'toVersion' => $this->createMockedAvailableMigration($versionClass3)->getVersion(),
            'expectedMigrationClasses' => [$versionClass3],
        ];
    }

    /**
     * @param string[] $availableMigrationClasses
     * @param string[] $orderedMigrationClassesFromLock
     * @param string[] $executedMigrationClasses
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator
     */
    private function getMigrationLockPlanCalculator(
        array $availableMigrationClasses,
        array $orderedMigrationClassesFromLock,
        array $executedMigrationClasses = [],
    ): MigrationLockPlanCalculator {
        $availableMigrationsSet = $this->getAvailableMigrationsSet($availableMigrationClasses);
        $migrationsLockRepositoryMock = $this->createMock(MigrationsLockRepository::class);
        $migrationsLockRepositoryMock->method('getMigrations')->willReturn($availableMigrationsSet);

        $migrationsLockMock = $this->createMock(MigrationsLock::class);
        $migrationsLockMock->method('getOrderedInstalledMigrationClasses')->willReturn($orderedMigrationClassesFromLock);

        $migrationsLockComparator = new MigrationsLockComparator($migrationsLockMock);
        $metadataStorageMock = $this->createMock(MetadataStorage::class);
        $metadataStorageMock->method('getExecutedMigrations')->willReturn($this->getExecutedMigrationsList($executedMigrationClasses));
        $dependencyFactoryMock = $this->createMock(DependencyFactory::class);
        $dependencyFactoryMock->method('getMetadataStorage')->willReturn($metadataStorageMock);

        return new MigrationLockPlanCalculator($migrationsLockRepositoryMock, $migrationsLockComparator, $dependencyFactoryMock);
    }

    /**
     * @param string[] $executedMigrationClasses
     * @return \Doctrine\Migrations\Metadata\ExecutedMigrationsList
     */
    private function getExecutedMigrationsList(array $executedMigrationClasses): ExecutedMigrationsList
    {
        $executedMigrations = [];

        foreach ($executedMigrationClasses as $executedMigrationClass) {
            $executedMigrations[] = new ExecutedMigration(new Version($executedMigrationClass));
        }

        return new ExecutedMigrationsList($executedMigrations);
    }

    /**
     * @param string[] $availableMigrationClasses
     * @return \Doctrine\Migrations\Metadata\AvailableMigrationsSet
     */
    private function getAvailableMigrationsSet(array $availableMigrationClasses): AvailableMigrationsSet
    {
        $availableMigrations = [];

        foreach ($availableMigrationClasses as $availableMigrationClass) {
            $availableMigrations[] = $this->createMockedAvailableMigration($availableMigrationClass);
        }

        return new AvailableMigrationsSet($availableMigrations);
    }
}
