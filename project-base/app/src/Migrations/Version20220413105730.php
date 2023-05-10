<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220413105730 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts ADD transport_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD transport_watched_price NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD pickup_place_identifier VARCHAR(255) DEFAULT NULL');
        $this->sql('COMMENT ON COLUMN carts.transport_watched_price IS \'(DC2Type:money)\'');
        $this->sql('
            ALTER TABLE
                carts
            ADD
                CONSTRAINT FK_4E004AAC9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_4E004AAC9909C13F ON carts (transport_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
