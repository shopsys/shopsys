<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220729131426 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders DROP COLUMN is_over_limit');
        $this->sql('ALTER TABLE transports DROP COLUMN is_over_limit_transport');
        $this->sql('UPDATE orders SET status_id = 1 WHERE status_id = 5');
        $this->sql('DELETE FROM order_statuses WHERE type = 5');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
