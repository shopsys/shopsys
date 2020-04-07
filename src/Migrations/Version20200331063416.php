<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200331063416 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_series_products (
                product_series_id INT NOT NULL,
                product_id INT NOT NULL,
                PRIMARY KEY(product_series_id, product_id)
            )');
        $this->sql('CREATE INDEX IDX_D2DAC7C83CD88711 ON product_series_products (product_series_id)');
        $this->sql('CREATE INDEX IDX_D2DAC7C84584665A ON product_series_products (product_id)');
        $this->sql('
            ALTER TABLE
                product_series_products
            ADD
                CONSTRAINT FK_D2DAC7C83CD88711 FOREIGN KEY (product_series_id) REFERENCES product_series (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_series_products
            ADD
                CONSTRAINT FK_D2DAC7C84584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
