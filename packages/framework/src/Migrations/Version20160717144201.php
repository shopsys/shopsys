<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160717144201 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE slider_items ADD hidden BOOLEAN NOT NULL DEFAULT FALSE;');
        $this->sql('ALTER TABLE slider_items ALTER hidden DROP DEFAULT;');
    }
}
