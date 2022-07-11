<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220711114131 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE country_domains DROP CONSTRAINT FK_4537E7F0F92F3E70');
        $this->sql('
            ALTER TABLE
                country_domains
            ADD
                CONSTRAINT FK_4537E7F0F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE administrator_grid_limits DROP CONSTRAINT FK_6FA77E4B09E92C');
        $this->sql('
            ALTER TABLE
                administrator_grid_limits
            ADD
                CONSTRAINT FK_6FA77E4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE transport_prices DROP CONSTRAINT FK_573018D09909C13F');
        $this->sql('
            ALTER TABLE
                transport_prices
            ADD
                CONSTRAINT FK_573018D09909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE transport_domains DROP CONSTRAINT FK_18AC7F6C9909C13F');
        $this->sql('
            ALTER TABLE
                transport_domains
            ADD
                CONSTRAINT FK_18AC7F6C9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE payment_domains DROP CONSTRAINT FK_9532B1774C3A3BB');
        $this->sql('
            ALTER TABLE
                payment_domains
            ADD
                CONSTRAINT FK_9532B1774C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE payment_prices DROP CONSTRAINT FK_C1F3F6CF4C3A3BB');
        $this->sql('
            ALTER TABLE
                payment_prices
            ADD
                CONSTRAINT FK_C1F3F6CF4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE order_items DROP CONSTRAINT FK_62809DB08D9F6D38');
        $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
