<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200522064838 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_variant_parameters (
                product_id INT NOT NULL,
                parameter_id INT NOT NULL,
                PRIMARY KEY(product_id, parameter_id)
            )');
        $this->sql('CREATE INDEX IDX_6C431174584665A ON product_variant_parameters (product_id)');
        $this->sql('CREATE INDEX IDX_6C431177C56DBD6 ON product_variant_parameters (parameter_id)');
        $this->sql('
            ALTER TABLE
                product_variant_parameters
            ADD
                CONSTRAINT FK_6C431174584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_variant_parameters
            ADD
                CONSTRAINT FK_6C431177C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
