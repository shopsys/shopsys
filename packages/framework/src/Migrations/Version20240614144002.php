<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240614144002 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_statuses ALTER type TYPE VARCHAR(25)');
        $this->sql('UPDATE order_statuses SET type = \'new\' WHERE type = \'1\'');
        $this->sql('UPDATE order_statuses SET type = \'in_progress\' WHERE type = \'2\'');
        $this->sql('UPDATE order_statuses SET type = \'done\' WHERE type = \'3\'');
        $this->sql('UPDATE order_statuses SET type = \'cancelled\' WHERE type = \'4\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
