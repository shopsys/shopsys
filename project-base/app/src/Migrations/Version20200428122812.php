<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200428122812 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE gopay_bank_swifts (
                id SERIAL NOT NULL,
                payment_method INT NOT NULL,
                swift VARCHAR(20) NOT NULL,
                name VARCHAR(50) NOT NULL,
                image_normal_url VARCHAR(255) NOT NULL,
                image_large_url VARCHAR(255) NOT NULL,
                is_online BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_FD7CD84A7B61A1F6 ON gopay_bank_swifts (payment_method)');
        $this->sql('CREATE UNIQUE INDEX gopay_bank_swift_unique ON gopay_bank_swifts (payment_method, swift)');
        $this->sql('
            CREATE TABLE gopay_payment_methods (
                id SERIAL NOT NULL,
                currency_id INT NOT NULL,
                identifier VARCHAR(20) NOT NULL,
                name VARCHAR(50) NOT NULL,
                image_normal_url VARCHAR(255) NOT NULL,
                image_large_url VARCHAR(255) NOT NULL,
                payment_group VARCHAR(20) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_B3CF6BD538248176 ON gopay_payment_methods (currency_id)');
        $this->sql('CREATE UNIQUE INDEX gopay_payment_method_unique ON gopay_payment_methods (currency_id, identifier)');
        $this->sql('
            ALTER TABLE
                gopay_bank_swifts
            ADD
                CONSTRAINT FK_FD7CD84A7B61A1F6 FOREIGN KEY (payment_method) REFERENCES gopay_payment_methods (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                gopay_payment_methods
            ADD
                CONSTRAINT FK_B3CF6BD538248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE payments ADD go_pay_payment_method_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE payments ADD type VARCHAR(255) NOT NULL DEFAULT \'basic\'');
        $this->sql('ALTER TABLE payments ALTER type DROP DEFAULT');
        $this->sql('
            ALTER TABLE
                payments
            ADD
                CONSTRAINT FK_65D29B32B1E3A4E9 FOREIGN KEY (go_pay_payment_method_id) REFERENCES gopay_payment_methods (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_65D29B32B1E3A4E9 ON payments (go_pay_payment_method_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
