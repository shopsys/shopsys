<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261013103000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts ADD removed_gift_product_ids JSON DEFAULT \'[]\' NOT NULL');
        $this->sql('ALTER TABLE carts ALTER removed_gift_product_ids DROP DEFAULT');
    }
}
