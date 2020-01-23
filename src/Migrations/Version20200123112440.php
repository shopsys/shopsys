<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200123112440 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE stocks_product (
                stock_id INT NOT NULL,
                product_id INT NOT NULL,
                product_quantity INT NOT NULL,
                PRIMARY KEY(stock_id, product_id)
            )');
        $this->sql('CREATE INDEX IDX_8C01C025DCD6110 ON stocks_product (stock_id)');
        $this->sql('CREATE INDEX IDX_8C01C0254584665A ON stocks_product (product_id)');
        $this->sql('
            ALTER TABLE
                stocks_product
            ADD
                CONSTRAINT FK_8C01C025DCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                stocks_product
            ADD
                CONSTRAINT FK_8C01C0254584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
