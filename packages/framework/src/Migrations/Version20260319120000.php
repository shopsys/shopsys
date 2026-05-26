<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260319120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payment_domains ADD COLUMN order_rounding_type VARCHAR(20) NOT NULL DEFAULT \'none\'');
        $this->sql('
            UPDATE payment_domains pd
            SET order_rounding_type = \'whole\'
            FROM payments p, setting_values sv, currencies c
            WHERE pd.payment_id = p.id
                AND p.czk_rounding = TRUE
                AND sv.domain_id = pd.domain_id
                AND sv.name = \'defaultDomainCurrencyId\'
                AND c.id = CAST(sv.value AS integer)
                AND c.code = \'CZK\'
        ');
        $this->sql('ALTER TABLE payment_domains ALTER COLUMN order_rounding_type DROP DEFAULT');
        $this->sql('ALTER TABLE payments DROP COLUMN czk_rounding');

        $this->sql('ALTER TABLE orders DROP COLUMN payment_czk_rounding');
    }
}
