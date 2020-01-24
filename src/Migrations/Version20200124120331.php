<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200124120331 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_stocks (
                stock_id INT NOT NULL,
                product_id INT NOT NULL,
                product_quantity INT NOT NULL,
                PRIMARY KEY(stock_id, product_id)
            )');
        $this->sql('CREATE INDEX IDX_348BD9A1DCD6110 ON product_stocks (stock_id)');
        $this->sql('CREATE INDEX IDX_348BD9A14584665A ON product_stocks (product_id)');
        $this->sql('
            ALTER TABLE
                product_stocks
            ADD
                CONSTRAINT FK_348BD9A1DCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_stocks
            ADD
                CONSTRAINT FK_348BD9A14584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
