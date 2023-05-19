<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000001;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000002;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000003;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000004;

class MigrationsLockTest extends AbstractMigrationLockTestCase
{
    public function testGetSkippedMigrationClasses(): void
    {
        $skippedMigrationClasses = $this->migrationsLock->getSkippedMigrationClasses();

        $this->assertCount(1, $skippedMigrationClasses);
        $this->assertContains(Version20180101000002::class, $skippedMigrationClasses);
    }

    public function testGetInstalledMigrationClasses(): void
    {
        $installedMigrationClasses = $this->migrationsLock->getOrderedInstalledMigrationClasses();

        $this->assertCount(2, $installedMigrationClasses);
        $this->assertContains(Version20180101000001::class, $installedMigrationClasses);
        $this->assertContains(Version20180101000003::class, $installedMigrationClasses);
    }

    public function testGetOrderedInstalledMigrationClasses(): void
    {
        $orderedMigrationClasses = $this->migrationsLock->getOrderedInstalledMigrationClasses();

        $migrationClassesByPositions = array_values($orderedMigrationClasses);
        $migrationPositionsByClasses = array_flip($migrationClassesByPositions);

        $this->assertGreaterThan(
            $migrationPositionsByClasses[Version20180101000003::class],
            $migrationPositionsByClasses[Version20180101000001::class],
        );
    }

    public function testSaveNewMigration(): void
    {
        $mockedAvailableMigration = $this->createMockedAvailableMigration(Version20180101000004::class);
        $availableMigrationList = new AvailableMigrationsList([$mockedAvailableMigration]);
        $this->migrationsLock->saveNewMigrations($availableMigrationList);

        $installedMigrationClasses = $this->createNewMigrationsLock()->getOrderedInstalledMigrationClasses();

        $this->assertCount(3, $installedMigrationClasses);
        $this->assertContains(Version20180101000004::class, $installedMigrationClasses);
    }

    public function testSaveAlreadyInstalledMigration(): void
    {
        $alreadyInstalledMockedAvailableMigration = $this->createMockedAvailableMigration(Version20180101000001::class);
        $availableMigrationList = new AvailableMigrationsList([$alreadyInstalledMockedAvailableMigration]);

        $this->migrationsLock->saveNewMigrations($availableMigrationList);

        $installedMigrationClasses = $this->createNewMigrationsLock()->getOrderedInstalledMigrationClasses();

        $this->assertCount(2, $installedMigrationClasses);
    }
}
