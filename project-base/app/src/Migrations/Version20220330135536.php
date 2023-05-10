<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220330135536 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE cart_promo_codes (
                cart_id INT NOT NULL,
                promo_code_id INT NOT NULL,
                PRIMARY KEY(cart_id, promo_code_id)
            )');
        $this->sql('CREATE INDEX IDX_5F57049B1AD5CDBF ON cart_promo_codes (cart_id)');
        $this->sql('CREATE INDEX IDX_5F57049B2FAE4625 ON cart_promo_codes (promo_code_id)');
        $this->sql('
            ALTER TABLE
                cart_promo_codes
            ADD
                CONSTRAINT FK_5DE6E36C1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                cart_promo_codes
            ADD
                CONSTRAINT FK_5DE6E36C2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
