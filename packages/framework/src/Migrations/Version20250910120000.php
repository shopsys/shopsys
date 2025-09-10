<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250910120000 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD COLUMN promotion_x INT DEFAULT NULL');
        $this->sql('ALTER TABLE products ADD COLUMN promotion_y INT DEFAULT NULL');

        $this->sql('ALTER TABLE flags ADD COLUMN promotion_x INT DEFAULT NULL');
        $this->sql('ALTER TABLE flags ADD COLUMN promotion_y INT DEFAULT NULL');
    }
}
