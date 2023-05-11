<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Yaml\Yaml;

class MigrationsLock
{
    protected string $migrationsLockFilePath;

    /**
     * @var array|null
     */
    protected ?array $parsedMigrationsLock = null;

    protected LoggerInterface $logger;

    /**
     * @param string $migrationsLockFilePath
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(string $migrationsLockFilePath, LoggerInterface $logger)
    {
        $this->migrationsLockFilePath = $migrationsLockFilePath;
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public function getOrderedInstalledMigrationClasses(): array
    {
        $orderedInstalledMigrationClasses = [];
        foreach ($this->load() as $class => $item) {
            if ($item['skip'] === false) {
                $orderedInstalledMigrationClasses[] = $class;
            }
        }

        return $orderedInstalledMigrationClasses;
    }

    /**
     * @param \Doctrine\Migrations\Metadata\AvailableMigration[] $availableMigrations
     * @return \Doctrine\Migrations\Metadata\AvailableMigration[]
     */
    public function filterOutSkippedMigrations(array $availableMigrations): array
    {
        $this->checkMigrationsMarkedAsInstalledInLock($availableMigrations);

        return $this->getNonSkippedAvailableMigrations($availableMigrations);
    }

    /**
     * @return string[]
     */
    public function getSkippedMigrationClasses(): array
    {
        $skippedMigrationClasses = [];
        foreach ($this->load() as $class => $item) {
            if ($item['skip'] === true) {
                $skippedMigrationClasses[] = $class;
            }
        }

        return $skippedMigrationClasses;
    }

    /**
     * @param \Doctrine\Migrations\Metadata\AvailableMigrationsList $availableMigrationsList
     */
    public function saveNewMigrations(AvailableMigrationsList $availableMigrationsList): void
    {
        $this->load();

        foreach ($availableMigrationsList->getItems() as $availableMigration) {
            $version = (string)$availableMigration->getVersion();
            if (!array_key_exists($version, $this->parsedMigrationsLock)) {
                $this->parsedMigrationsLock[$version] = [
                    'skip' => false,
                ];
            }
        }

        $this->save();
    }

    /**
     * @return array
     */
    protected function load(): array
    {
        if ($this->parsedMigrationsLock === null) {
            $this->parsedMigrationsLock = [];

            if (file_exists($this->migrationsLockFilePath)) {
                $this->parsedMigrationsLock = Yaml::parseFile($this->migrationsLockFilePath);
            }
        }

        return $this->parsedMigrationsLock;
    }

    protected function save(): void
    {
        $content = Yaml::dump($this->parsedMigrationsLock);

        file_put_contents($this->migrationsLockFilePath, $content);
    }

    /**
     * @param \Doctrine\Migrations\Metadata\AvailableMigration[] $availableMigrations
     */
    protected function checkMigrationsMarkedAsInstalledInLock(array $availableMigrations): void
    {
        foreach ($this->getOrderedInstalledMigrationClasses() as $migrationClass) {
            if (!array_key_exists($migrationClass, $availableMigrations)) {
                $message = sprintf(
                    'Migration version "%s" marked as installed in migration lock file was not found!',
                    $migrationClass
                );
                $this->logger->log(LogLevel::WARNING, $message);
            }
        }
    }

    /**
     * @param \Doctrine\Migrations\Metadata\AvailableMigration[] $availableMigrations
     * @return \Doctrine\Migrations\Metadata\AvailableMigration[]
     */
    protected function getNonSkippedAvailableMigrations(array $availableMigrations): array
    {
        foreach ($this->getSkippedMigrationClasses() as $skippedMigrationClass) {
            if (array_key_exists($skippedMigrationClass, $availableMigrations)) {
                unset($availableMigrations[$skippedMigrationClass]);
            } else {
                $message = sprintf(
                    'Migration version "%s" marked as skipped in migration lock file was not found!',
                    $skippedMigrationClass
                );
                $this->logger->log(LogLevel::WARNING, $message);
            }
        }

        return $availableMigrations;
    }
}
