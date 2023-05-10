<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220421121900 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts ADD payment_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD payment_watched_price NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('COMMENT ON COLUMN carts.payment_watched_price IS \'(DC2Type:money)\'');
        $this->sql('ALTER TABLE carts ADD payment_go_pay_bank_swift VARCHAR(15) DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                carts
            ADD
                CONSTRAINT FK_4E004AAC4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_4E004AAC4C3A3BB ON carts (payment_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
