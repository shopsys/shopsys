<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Metadata\MigrationPlan;
use Doctrine\Migrations\Metadata\MigrationPlanList;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\MigrationPlanCalculator;
use Doctrine\Migrations\Version\Version;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\MethodIsNotAllowedException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\PartialMigrationNotAllowedException;

class MigrationLockPlanCalculator implements MigrationPlanCalculator
{
    protected MigrationsLockRepository $migrationsLockRepository;

    protected MetadataStorage $metadataStorage;

    protected MigrationsLockComparator $migrationsLockComparator;

    /**
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockRepository $migrationsLockRepository
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockComparator $migrationsLockComparator
     * @param \Doctrine\Migrations\DependencyFactory $dependencyFactory
     */
    public function __construct(
        MigrationsLockRepository $migrationsLockRepository,
        MigrationsLockComparator $migrationsLockComparator,
        DependencyFactory $dependencyFactory
    ) {
        $this->migrationsLockRepository = $migrationsLockRepository;
        $this->migrationsLockComparator = $migrationsLockComparator;
        $this->metadataStorage = $dependencyFactory->getMetadataStorage();
    }

    /**
     * @param array $versions
     * @param string $direction
     * @return \Doctrine\Migrations\Metadata\MigrationPlanList
     */
    public function getPlanForVersions(array $versions, string $direction): MigrationPlanList
    {
        throw new MethodIsNotAllowedException(sprintf('Method "%s" is not supported, use "getPlanUntilVersion"', __METHOD__));
    }

    /**
     * @param \Doctrine\Migrations\Version\Version $to
     * @return \Doctrine\Migrations\Metadata\MigrationPlanList
     */
    public function getPlanUntilVersion(Version $to): MigrationPlanList
    {
        $migrationsToExecute = [];
        $availableMigrations = $this->getMigrations()->getItems();
        $executedMigrations = $this->metadataStorage->getExecutedMigrations()->getItems();

        foreach ($availableMigrations as $availableMigration) {
            if ($this->migrationsLockComparator->compare($to, $availableMigration->getVersion()) < 0) {
                throw new PartialMigrationNotAllowedException('Partial migration up in not allowed. Only up migration of all registered versions is supported because of multiple sources of migrations.');
            }

            if ($this->shouldExecuteMigration($availableMigration, $executedMigrations)) {
                $migrationsToExecute[] = new MigrationPlan($availableMigration->getVersion(), $availableMigration->getMigration(), Direction::UP);
            }
        }

        return new MigrationPlanList($migrationsToExecute, Direction::UP);
    }

    /**
     * @return \Doctrine\Migrations\Metadata\AvailableMigrationsList
     */
    public function getMigrations(): AvailableMigrationsList
    {
        $availableMigrations = $this->migrationsLockRepository->getMigrations()->getItems();
        uasort($availableMigrations, function (AvailableMigration $a, AvailableMigration $b): int {
            return $this->migrationsLockComparator->compare($a->getVersion(), $b->getVersion());
        });

        return new AvailableMigrationsList($availableMigrations);
    }

    /**
     * @param \Doctrine\Migrations\Metadata\AvailableMigration $availableMigration
     * @param \Doctrine\Migrations\Metadata\ExecutedMigration[] $executedMigrations
     * @return bool
     */
    protected function shouldExecuteMigration(AvailableMigration $availableMigration, array $executedMigrations): bool
    {
        foreach ($executedMigrations as $executedMigration) {
            if ($availableMigration->getVersion()->equals($executedMigration->getVersion())) {
                return false;
            }
        }
        return true;
    }
}
