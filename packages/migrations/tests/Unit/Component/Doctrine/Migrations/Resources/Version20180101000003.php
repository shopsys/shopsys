<?php

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @see \Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\MigrationsLockTest
 */
class Version20180101000003 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
    }
}
