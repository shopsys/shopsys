<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200128112829 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_codes DROP "code"');
        $this->sql('ALTER TABLE promo_codes ADD "code" text NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE promo_codes ALTER "code" DROP DEFAULT');
        $this->sql('CREATE UNIQUE INDEX domain_code_unique ON promo_codes (domain_id, code)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
