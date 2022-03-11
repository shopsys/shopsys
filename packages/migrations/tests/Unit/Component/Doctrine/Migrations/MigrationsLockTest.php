<?php

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Version\Version;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000001;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000002;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000003;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000004;

class MigrationsLockTest extends TestCase
{
    private const MIGRATION_LOCK_TEMPLATE = __DIR__ . '/Resources/migrations-lock.yml';
    private const MIGRATION_LOCK = __DIR__ . '/Resources/migrations-lock.yml.tmp';

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock
     */
    private $migrationsLock;

    protected function setUp(): void
    {
        copy(self::MIGRATION_LOCK_TEMPLATE, self::MIGRATION_LOCK);
        $this->migrationsLock = $this->createNewMigrationsLock();
    }

    protected function tearDown(): void
    {
        unlink(self::MIGRATION_LOCK);
    }

    /**
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock
     */
    private function createNewMigrationsLock(): MigrationsLock
    {
        $loggerMock = $this->createMock(Logger::class);

        return new MigrationsLock(self::MIGRATION_LOCK, $loggerMock);
    }

    public function testGetSkippedMigrationClasses()
    {
        $skippedMigrationClasses = $this->migrationsLock->getSkippedMigrationClasses();

        $this->assertCount(1, $skippedMigrationClasses);
        $this->assertContains(Version20180101000002::class, $skippedMigrationClasses);
    }

    public function testGetInstalledMigrationClasses()
    {
        $installedMigrationClasses = $this->migrationsLock->getOrderedInstalledMigrationClasses();

        $this->assertCount(2, $installedMigrationClasses);
        $this->assertContains(Version20180101000001::class, $installedMigrationClasses);
        $this->assertContains(Version20180101000003::class, $installedMigrationClasses);
    }

    public function testGetOrderedInstalledMigrationClasses()
    {
        $orderedMigrationClasses = $this->migrationsLock->getOrderedInstalledMigrationClasses();

        $migrationClassesByPositions = array_values($orderedMigrationClasses);
        $migrationPositionsByClasses = array_flip($migrationClassesByPositions);

        $this->assertGreaterThan(
            $migrationPositionsByClasses[Version20180101000003::class],
            $migrationPositionsByClasses[Version20180101000001::class]
        );
    }

    public function testSaveNewMigration()
    {
        $mockedAvailableMigration = $this->createMockedAvailableMigration(Version20180101000004::class);
        $availableMigrationList = new AvailableMigrationsList([$mockedAvailableMigration]);
        $this->migrationsLock->saveNewMigrations($availableMigrationList);

        $installedMigrationClasses = $this->createNewMigrationsLock()->getOrderedInstalledMigrationClasses();

        $this->assertCount(3, $installedMigrationClasses);
        $this->assertContains(Version20180101000004::class, $installedMigrationClasses);
    }

    public function testSaveAlreadyInstalledMigration()
    {
        $alreadyInstalledMockedAvailableMigration = $this->createMockedAvailableMigration(Version20180101000001::class);
        $availableMigrationList = new AvailableMigrationsList([$alreadyInstalledMockedAvailableMigration]);

        $this->migrationsLock->saveNewMigrations($availableMigrationList);

        $installedMigrationClasses = $this->createNewMigrationsLock()->getOrderedInstalledMigrationClasses();

        $this->assertCount(2, $installedMigrationClasses);
    }

    /**
     * @param string $className
     * @return \Doctrine\Migrations\Metadata\AvailableMigration
     */
    private function createMockedAvailableMigration(string $className): AvailableMigration
    {
        /** @var \Doctrine\Migrations\AbstractMigration|\PHPUnit\Framework\MockObject\MockObject $migrationMock */
        $migrationMock = $this->createMock($className);

        return new AvailableMigration(new Version($className), $migrationMock);
    }
}
