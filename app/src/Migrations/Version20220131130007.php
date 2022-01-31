<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220131130007 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE payment_transactions (
                id SERIAL NOT NULL,
                order_id INT NOT NULL,
                payment_id INT DEFAULT NULL,
                external_payment_identifier VARCHAR(200) NOT NULL,
                external_payment_status VARCHAR(255) DEFAULT NULL,
                paid_amount NUMERIC(20, 6) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_8C58AD568D9F6D38 ON payment_transactions (order_id)');
        $this->sql('CREATE INDEX IDX_8C58AD564C3A3BB ON payment_transactions (payment_id)');
        $this->sql('COMMENT ON COLUMN payment_transactions.paid_amount IS \'(DC2Type:money)\'');
        $this->sql('
            ALTER TABLE
                payment_transactions
            ADD
                CONSTRAINT FK_8C58AD568D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                payment_transactions
            ADD
                CONSTRAINT FK_8C58AD564C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE
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
