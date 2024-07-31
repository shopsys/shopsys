<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240726150741 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD transport_watched_price NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD payment_watched_price NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('COMMENT ON COLUMN orders.transport_watched_price IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN orders.payment_watched_price IS \'(DC2Type:money)\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
