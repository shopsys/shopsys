<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200714072919 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
