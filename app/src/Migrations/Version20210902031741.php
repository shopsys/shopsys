<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210902031741 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE related_products (
                main_product INT NOT NULL,
                related_product INT NOT NULL,
                PRIMARY KEY(main_product, related_product)
            )');
        $this->sql('CREATE INDEX IDX_153914F72701AD44 ON related_products (main_product)');
        $this->sql('CREATE INDEX IDX_153914F7EC53CE08 ON related_products (related_product)');
        $this->sql('
            ALTER TABLE
                related_products
            ADD
                CONSTRAINT FK_153914F72701AD44 FOREIGN KEY (main_product) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                related_products
            ADD
                CONSTRAINT FK_153914F7EC53CE08 FOREIGN KEY (related_product) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
