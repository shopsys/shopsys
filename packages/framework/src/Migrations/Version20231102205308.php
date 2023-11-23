<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231102205308 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            ALTER TABLE
                orders
            ADD
                order_payment_status_page_valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD order_payment_status_page_validity_hash UUID DEFAULT NULL');
        $this->sql('ALTER TABLE orders ALTER order_payment_status_page_validity_hash DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
