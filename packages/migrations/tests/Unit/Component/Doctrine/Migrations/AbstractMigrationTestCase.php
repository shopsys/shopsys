<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Version\Version;
use PHPUnit\Framework\TestCase;

abstract class AbstractMigrationTestCase extends TestCase
{
    protected function createMockedAvailableMigration(string $className): AvailableMigration
    {
        /** @var \Doctrine\Migrations\AbstractMigration&\PHPUnit\Framework\MockObject\Stub $migrationStub */
        $migrationStub = $this->createStub($className);

        return new AvailableMigration(new Version($className), $migrationStub);
    }
}
