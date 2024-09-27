<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240926162253 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE inquiries (
                id SERIAL NOT NULL,
                product_id INT DEFAULT NULL,
                product_catnum VARCHAR(100) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                telephone VARCHAR(30) NOT NULL,
                company_name VARCHAR(100) DEFAULT NULL,
                company_number VARCHAR(50) DEFAULT NULL,
                company_tax_number VARCHAR(50) DEFAULT NULL,
                note TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_1CCE4D54584665A ON inquiries (product_id)');
        $this->sql('
            ALTER TABLE
                inquiries
            ADD
                CONSTRAINT FK_1CCE4D54584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE
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
