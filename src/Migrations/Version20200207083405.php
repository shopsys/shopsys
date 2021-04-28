<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200207083405 extends AbstractMigration
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
            CREATE TABLE stocks (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                is_default BOOLEAN NOT NULL,
                external_id VARCHAR(255) DEFAULT NULL,
                note TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_56F798059F75D7B0 ON stocks (external_id)');
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

        $this->sql('
            CREATE TABLE stock_domains (
                id SERIAL NOT NULL,
                stock_id INT NOT NULL,
                domain_id INT NOT NULL,
                is_enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_95DDAF3EDCD6110 ON stock_domains (stock_id)');
        $this->sql('CREATE UNIQUE INDEX stock_domain ON stock_domains (stock_id, domain_id)');
        $this->sql('
            ALTER TABLE
                stock_domains
            ADD
                CONSTRAINT FK_95DDAF3EDCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
