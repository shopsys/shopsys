<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Exception\DuplicateMigrationVersion;
use Doctrine\Migrations\Exception\MigrationClassNotFound;
use Doctrine\Migrations\Finder\MigrationFinder;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\AvailableMigrationsSet;
use Doctrine\Migrations\MigrationsRepository;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\Migrations\Version\Version;
use Override;

/**
 * This class heavily inspired by @see \Doctrine\Migrations\FilesystemMigrationsRepository
 * It also fetches the migrations from the filesystem but in addition, it takes migration-lock.yml into account
 */
class MigrationsLockRepository implements MigrationsRepository
{
    /**
     * @var \Doctrine\Migrations\Metadata\AvailableMigration[]
     */
    protected array $availableMigrations = [];

    /**
     * @var \Doctrine\Migrations\Metadata\AvailableMigration[]
     */
    protected ?array $filteredAvailableMigrations = null;

    /**
     * @var array<string,string>
     */
    protected array $migrationDirectories;

    protected MigrationFinder $migrationFinder;

    protected MigrationFactory $migrationFactory;

    public function __construct(
        protected readonly MigrationsLock $migrationsLock,
        DependencyFactory $dependencyFactory,
    ) {
        $configuration = $dependencyFactory->getConfiguration();
        $this->migrationDirectories = $configuration->getMigrationDirectories();
        $this->migrationFinder = $dependencyFactory->getMigrationsFinder();
        $this->migrationFactory = $dependencyFactory->getMigrationFactory();

        $this->registerMigrations($configuration->getMigrationClasses());
    }

    /**
     * @param string[] $migrationClasses
     */
    protected function registerMigrations(array $migrationClasses): void
    {
        foreach ($migrationClasses as $class) {
            $this->registerMigration($class);
        }
    }

    protected function registerMigration(string $migrationClassName): void
    {
        $this->ensureMigrationClassExists($migrationClassName);

        $version = new Version($migrationClassName);
        $migration = $this->migrationFactory->createVersion($migrationClassName);

        $this->registerMigrationInstance($version, $migration);
    }

    protected function ensureMigrationClassExists(string $class): void
    {
        if (!class_exists($class)) {
            throw MigrationClassNotFound::new($class);
        }
    }

    protected function registerMigrationInstance(Version $version, AbstractMigration $migration): void
    {
        if (array_key_exists((string)$version, $this->availableMigrations)) {
            throw DuplicateMigrationVersion::new(
                (string)$version,
                get_class($migration),
            );
        }

        $this->availableMigrations[(string)$version] = new AvailableMigration($version, $migration);
    }

    #[Override]
    public function hasMigration(string $version): bool
    {
        $this->loadMigrationsFromDirectories();

        return array_key_exists($version, $this->filteredAvailableMigrations);
    }

    #[Override]
    public function getMigration(Version $version): AvailableMigration
    {
        $this->loadMigrationsFromDirectories();

        if (!array_key_exists((string)$version, $this->filteredAvailableMigrations)) {
            throw MigrationClassNotFound::new((string)$version);
        }

        return $this->filteredAvailableMigrations[(string)$version];
    }

    #[Override]
    public function getMigrations(): AvailableMigrationsSet
    {
        $this->loadMigrationsFromDirectories();

        return new AvailableMigrationsSet($this->filteredAvailableMigrations);
    }

    protected function loadMigrationsFromDirectories(): void
    {
        if ($this->filteredAvailableMigrations !== null) {
            return;
        }

        foreach ($this->migrationDirectories as $namespace => $path) {
            $migrations = $this->migrationFinder->findMigrations(
                $path,
                $namespace,
            );
            $this->registerMigrations($migrations);
        }
        $this->filteredAvailableMigrations = $this->migrationsLock->filterOutSkippedMigrations($this->availableMigrations);
    }
}
