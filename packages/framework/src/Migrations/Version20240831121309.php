<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240831121309 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_excluded_transports (
                product_id INT NOT NULL,
                transport_id INT NOT NULL,
                PRIMARY KEY(product_id, transport_id)
            )');
        $this->sql('CREATE INDEX IDX_E13A2FA44584665A ON product_excluded_transports (product_id)');
        $this->sql('CREATE INDEX IDX_E13A2FA49909C13F ON product_excluded_transports (transport_id)');
        $this->sql('
            ALTER TABLE
                product_excluded_transports
            ADD
                CONSTRAINT FK_7A9C76504584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_excluded_transports
            ADD
                CONSTRAINT FK_7A9C76509909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
