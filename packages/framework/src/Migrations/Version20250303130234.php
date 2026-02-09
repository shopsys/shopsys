<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250303130234 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_code_limit DROP CONSTRAINT promo_code_limit_pkey');
        $this->sql('ALTER TABLE promo_code_limit RENAME COLUMN from_price_with_vat TO from_price');
        $this->sql('ALTER TABLE promo_code_limit ADD PRIMARY KEY (promo_code_id, from_price)');
    }
}
