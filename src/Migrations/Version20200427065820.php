<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200427065820 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_packages (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                position INT NOT NULL,
                length INT DEFAULT NULL,
                width INT DEFAULT NULL,
                height INT DEFAULT NULL,
                weight DOUBLE PRECISION DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_5005AA304584665A ON product_packages (product_id)');
        $this->sql('CREATE UNIQUE INDEX product_package ON product_packages (product_id, position)');
        $this->sql('
            ALTER TABLE
                product_packages
            ADD
                CONSTRAINT FK_5005AA304584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE product_domains ADD mounting_state BOOLEAN DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD embedded_accessories VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD package_not_included VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD packaging_unit INT DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD count_packages INT DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD total_package_weight DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
