<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220201120501 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payment_transactions ADD refunded_amount NUMERIC(20, 6) NOT NULL DEFAULT \'0\'');
        $this->sql('COMMENT ON COLUMN payment_transactions.refunded_amount IS \'(DC2Type:money)\'');
        $this->sql('ALTER TABLE payment_transactions ALTER refunded_amount DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
