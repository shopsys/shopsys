<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170728120619 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
        $this->sql('ALTER TABLE cart_items ALTER added_at DROP DEFAULT');
    }
}
