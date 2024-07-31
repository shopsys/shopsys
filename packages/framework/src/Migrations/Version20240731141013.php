<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240731141013 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE SEQUENCE complaint_items_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->sql('CREATE SEQUENCE complaints_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->sql('
            CREATE TABLE complaint_items (
                id INT NOT NULL,
                complaint_id INT NOT NULL,
                order_item_id INT NOT NULL,
                quantity INT NOT NULL,
                description TEXT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_A0C592FBEDAE188E ON complaint_items (complaint_id)');
        $this->sql('CREATE INDEX IDX_A0C592FBE415FB15 ON complaint_items (order_item_id)');
        $this->sql('
            CREATE TABLE complaints (
                id INT NOT NULL,
                order_id INT NOT NULL,
                customer_user_id INT DEFAULT NULL,
                delivery_country_id INT DEFAULT NULL,
                uuid UUID NOT NULL,
                number VARCHAR(30) NOT NULL,
                delivery_first_name VARCHAR(100) DEFAULT NULL,
                delivery_last_name VARCHAR(100) DEFAULT NULL,
                delivery_company_name VARCHAR(100) DEFAULT NULL,
                delivery_telephone VARCHAR(30) DEFAULT NULL,
                delivery_street VARCHAR(100) NOT NULL,
                delivery_city VARCHAR(100) NOT NULL,
                delivery_postcode VARCHAR(30) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                status VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_A05AAF3AD17F50A6 ON complaints (uuid)');
        $this->sql('CREATE UNIQUE INDEX UNIQ_A05AAF3A96901F54 ON complaints (number)');
        $this->sql('CREATE INDEX IDX_A05AAF3A8D9F6D38 ON complaints (order_id)');
        $this->sql('CREATE INDEX IDX_A05AAF3ABBB3772B ON complaints (customer_user_id)');
        $this->sql('CREATE INDEX IDX_A05AAF3AE76AA954 ON complaints (delivery_country_id)');
        $this->sql('
            ALTER TABLE
                complaint_items
            ADD
                CONSTRAINT FK_A0C592FBEDAE188E FOREIGN KEY (complaint_id) REFERENCES complaints (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                complaint_items
            ADD
                CONSTRAINT FK_A0C592FBE415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                complaints
            ADD
                CONSTRAINT FK_A05AAF3A8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                complaints
            ADD
                CONSTRAINT FK_A05AAF3ABBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                complaints
            ADD
                CONSTRAINT FK_A05AAF3AE76AA954 FOREIGN KEY (delivery_country_id) REFERENCES countries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
