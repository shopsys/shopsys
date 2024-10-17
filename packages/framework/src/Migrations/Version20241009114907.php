<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241009114907 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_codes ALTER discount_type TYPE VARCHAR(20)');
        $this->sql('UPDATE promo_codes SET discount_type = \'percent\' WHERE discount_type = \'1\'');
        $this->sql('UPDATE promo_codes SET discount_type = \'nominal\' WHERE discount_type = \'2\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
