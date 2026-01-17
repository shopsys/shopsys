<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160717105120 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE articles ADD hidden BOOLEAN NOT NULL DEFAULT FALSE;');
        $this->sql('ALTER TABLE articles ALTER hidden DROP DEFAULT;');
    }
}
