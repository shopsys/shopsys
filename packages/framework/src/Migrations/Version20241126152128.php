<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241126152128 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_codes ALTER discount_type TYPE VARCHAR(25)');
        $this->sql('UPDATE promo_codes SET discount_type = \'percent\' WHERE discount_type = \'1\'');
        $this->sql('UPDATE promo_codes SET discount_type = \'nominal\' WHERE discount_type = \'2\'');
    }
}
