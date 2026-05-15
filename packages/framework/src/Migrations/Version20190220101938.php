<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20190220101938 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'UPDATE setting_values SET type = \'money\' WHERE name = \'freeTransportAndPaymentPriceLimit\' AND type != \'none\'',
        );
    }
}
