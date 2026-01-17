<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * @see \Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\MigrationsLockTest
 */
class Version20180101000003 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
    }
}
