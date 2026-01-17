<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240813071845 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE adverts SET position_name = \'productListSecondRow\' WHERE position_name = \'productListMiddle\'');
        $this->sql('UPDATE adverts SET position_name = \'productListSecondRow\' WHERE position_name = \'productList\'');
    }
}
