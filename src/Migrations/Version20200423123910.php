<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200423123910 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD selling_price_with_vat NUMERIC(20, 6) NOT NULL DEFAULT 0');
        $this->sql('COMMENT ON COLUMN product_domains.selling_price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('UPDATE product_domains SET selling_price_with_vat = COALESCE(low_price_with_vat, high_price_with_vat, 0)');
        $this->sql('ALTER TABLE product_domains ALTER selling_price_with_vat DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
