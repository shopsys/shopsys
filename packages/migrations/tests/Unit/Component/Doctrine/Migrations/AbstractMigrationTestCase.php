<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Version\Version;
use PHPUnit\Framework\TestCase;

abstract class AbstractMigrationTestCase extends TestCase
{
    /**
     * @param class-string $className
     * @return \Doctrine\Migrations\Metadata\AvailableMigration
     */
    protected function createMockedAvailableMigration(string $className): AvailableMigration
    {
        /** @var \Doctrine\Migrations\AbstractMigration|\PHPUnit\Framework\MockObject\MockObject $migrationMock */
        $migrationMock = $this->createMock($className);

        return new AvailableMigration(new Version($className), $migrationMock);
    }
}
