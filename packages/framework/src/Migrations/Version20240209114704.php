<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240209114704 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE UNIQUE INDEX cart_identifier ON carts (cart_identifier) WHERE cart_identifier <> \'\';');
        $this->sql('CREATE UNIQUE INDEX customer_user_id ON carts (customer_user_id);');
    }
}
