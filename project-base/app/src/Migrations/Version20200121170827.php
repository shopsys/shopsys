<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200121170827 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD akeneo_code VARCHAR(100) DEFAULT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_3AF34668CC7118A2 ON categories (akeneo_code)');
    }
}
