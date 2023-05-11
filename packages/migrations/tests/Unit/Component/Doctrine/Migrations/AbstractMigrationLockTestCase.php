<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Psr\Log\LoggerInterface;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;

abstract class AbstractMigrationLockTestCase extends AbstractMigrationTestCase
{
    protected const MIGRATION_LOCK_TEMPLATE = __DIR__ . '/Resources/migrations-lock.yml';
    protected const MIGRATION_LOCK = __DIR__ . '/Resources/migrations-lock.yml.tmp';

    protected MigrationsLock $migrationsLock;

    protected function setUp(): void
    {
        copy(static::MIGRATION_LOCK_TEMPLATE, static::MIGRATION_LOCK);
        $this->migrationsLock = $this->createNewMigrationsLock();
    }

    protected function tearDown(): void
    {
        unlink(static::MIGRATION_LOCK);
    }

    /**
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock
     */
    protected function createNewMigrationsLock(): MigrationsLock
    {
        $loggerMock = $this->createMock(LoggerInterface::class);

        return new MigrationsLock(static::MIGRATION_LOCK, $loggerMock);
    }
}
