<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260710071411 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD paid BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE orders ALTER paid DROP DEFAULT');
        $this->sql('ALTER TABLE orders ADD paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('UPDATE orders SET paid = TRUE WHERE id IN (
            SELECT pt.order_id
            FROM payment_transactions pt
            JOIN payments p ON p.id = pt.payment_id
            WHERE p.type = \'goPay\' AND pt.external_payment_status = \'PAID\'
        )');
        $this->sql('UPDATE orders SET paid = TRUE WHERE paid = FALSE AND status_id IN (
            SELECT id
            FROM order_statuses
            WHERE type = \'done\'
        )');
    }
}
