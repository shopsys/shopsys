<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230405123121 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX name_domain');
        $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (name, domain_id)');

        $this->sql('UPDATE mail_templates SET order_status_id = 1 WHERE name = \'order_status_1\'');
        $this->sql('UPDATE mail_templates SET order_status_id = 2 WHERE name = \'order_status_2\'');
        $this->sql('UPDATE mail_templates SET order_status_id = 3 WHERE name = \'order_status_3\'');
        $this->sql('UPDATE mail_templates SET order_status_id = 4 WHERE name = \'order_status_4\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
