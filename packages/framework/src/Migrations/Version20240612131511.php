<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240612131511 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE unit_translations ADD CONSTRAINT FK_142138102C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES units (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE flag_translations ADD CONSTRAINT FK_2100ABA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES flags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE parameter_translations ADD CONSTRAINT FK_77C2A7492C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE delivery_addresses ADD CONSTRAINT FK_2BAF3984F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE cart_promo_codes ADD CONSTRAINT FK_5F57049B1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE cart_promo_codes ADD CONSTRAINT FK_5F57049B2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE navigation_item_categories ADD CONSTRAINT FK_71699B7654ED5C2D FOREIGN KEY (navigation_item_id) REFERENCES navigation_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE navigation_item_categories ADD CONSTRAINT FK_71699B7612469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
