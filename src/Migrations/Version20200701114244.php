<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200701114244 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE setting_values SET type = \'string\'
            WHERE name = \'scontoBridgeTransferProductStockLastUpdatedDatetime\';');
        $this->sql('UPDATE setting_values SET type = \'string\'
            WHERE name = \'scontoBridgeTransferFutureProductStockLastUpdatedDatetime\';');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
