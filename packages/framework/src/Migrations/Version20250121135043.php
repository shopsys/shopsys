<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250121135043 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200219121349')) {
            $this->sql('ALTER TABLE slider_items ADD datetime_visible_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->sql('ALTER TABLE slider_items ADD datetime_visible_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function down(Schema $schema): void
    {
    }
}
