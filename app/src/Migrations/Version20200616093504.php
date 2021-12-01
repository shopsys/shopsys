<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200616093504 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_codes ADD on_sale BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE promo_codes ALTER on_sale DROP DEFAULT');
        $this->sql('ALTER TABLE promo_codes ADD in_action BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE promo_codes ALTER in_action DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
