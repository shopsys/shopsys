<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210421103608 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE stores (
                id SERIAL NOT NULL,
                stock_id INT DEFAULT NULL,
                is_default BOOLEAN NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                external_id VARCHAR(255) NULL,
                address TEXT DEFAULT NULL,
                opening_hours TEXT DEFAULT NULL,
                contact_info TEXT DEFAULT NULL,
                special_message TEXT DEFAULT NULL,
                location_latitude NUMERIC(16, 13) DEFAULT NULL,
                location_longitude NUMERIC(16, 13) DEFAULT NULL,
                position INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_D5907CCC9F75D7B0 ON stores (external_id)');
        $this->sql('
            CREATE TABLE store_domains (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                store_id INT NOT NULL,
                is_enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_95739E7AB092A811 ON store_domains (store_id)');
        $this->sql('
            ALTER TABLE
                store_domains
            ADD
                CONSTRAINT FK_95739E7AB092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX store_domain ON store_domains (store_id, domain_id)');
        $this->sql('
            ALTER TABLE
                stores
            ADD
                CONSTRAINT FK_D5907CCCDCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_D5907CCCDCD6110 ON stores (stock_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
