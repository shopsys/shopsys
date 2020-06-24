<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200623103836 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_stocks ADD future_product_quantity INT DEFAULT NULL');
        $this->sql('ALTER TABLE product_stocks ADD date_of_storage TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
            (\'scontoBridgeTransferFutureProductStockLastUpdatedDatetime\', 0, \'1970-01-01T00:00:00+0000\', \'datetime\')
        ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
