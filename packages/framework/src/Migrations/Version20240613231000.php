<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240613231000 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX idx_bf6e22b08b8e8428');
        $this->sql('ALTER TABLE navigation_item_categories DROP CONSTRAINT fk_b2a413d84f43d067');
        $this->sql('ALTER TABLE navigation_item_categories DROP CONSTRAINT fk_b2a413d812469de2');
        $this->sql('ALTER TABLE parameter_translations DROP CONSTRAINT fk_16f42d262c2ac5d3');
        $this->sql('ALTER TABLE unit_translations DROP CONSTRAINT fk_15c4c1de2c2ac5d3');
        $this->sql('ALTER TABLE flag_translations DROP CONSTRAINT fk_23d1ba1a2c2ac5d3');
        $this->sql('ALTER TABLE cart_promo_codes DROP CONSTRAINT fk_5de6e36c1ad5cdbf');
        $this->sql('ALTER TABLE cart_promo_codes DROP CONSTRAINT fk_5de6e36c2fae4625');
        $this->sql('ALTER TABLE delivery_addresses DROP CONSTRAINT fk_2baf3984e76aa954');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
