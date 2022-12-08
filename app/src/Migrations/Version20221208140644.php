<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20221208140644 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX countries_code_uni');
        $this->sql('DROP INDEX idx_e52ffdeea76ed395');
        $this->sql('DROP INDEX idx_4e004aaca76ed395');
        $this->sql('ALTER TABLE promo_codes DROP apply_on_second_product');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
