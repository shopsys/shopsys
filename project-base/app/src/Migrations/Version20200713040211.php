<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200713040211 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE slider_items ADD gtm_id TEXT NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE slider_items ALTER gtm_id DROP DEFAULT');

        $this->sql('ALTER TABLE slider_items ADD gtm_creative TEXT DEFAULT NULL');
    }
}
