<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200811103617 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE future_product_stocks (
                id SERIAL NOT NULL,
                erp_id VARCHAR(50) NOT NULL,
                store_code VARCHAR(100) NOT NULL,
                sku VARCHAR(100) NOT NULL,
                amount INT NOT NULL,
                date_expected_arrival TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                date_confirmed_arrival TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                is_late BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_40DDF3D498D6305B ON future_product_stocks (erp_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
