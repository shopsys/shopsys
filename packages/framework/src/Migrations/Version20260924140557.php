<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260924140557 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD transport_watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD payment_watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');
    }
}
