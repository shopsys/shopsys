<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250120145445 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200714072919')) {
            $this->sql('
            CREATE TABLE notification_bars (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                text TEXT NOT NULL,
                validity_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                validity_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                rgb_color VARCHAR(7) NOT NULL,
                hidden BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        }
    }
}
