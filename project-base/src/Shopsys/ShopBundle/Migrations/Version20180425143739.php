<?php

namespace Shopsys\ShopBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180425143739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE articles ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
        $this->sql('ALTER TABLE articles ALTER created_at DROP DEFAULT;');
    }

    public function down(Schema $schema): void
    {
    }
}
