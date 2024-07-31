<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240716132551 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE order_promo_codes (
                order_id INT NOT NULL,
                promo_code_id INT NOT NULL,
                PRIMARY KEY(order_id, promo_code_id)
            )');
        $this->sql('CREATE INDEX IDX_1736825D8D9F6D38 ON order_promo_codes (order_id)');
        $this->sql('CREATE INDEX IDX_1736825D2FAE4625 ON order_promo_codes (promo_code_id)');
        $this->sql('
            ALTER TABLE
                order_promo_codes
            ADD
                CONSTRAINT FK_1736825D8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                order_promo_codes
            ADD
                CONSTRAINT FK_1736825D2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
