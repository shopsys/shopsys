<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230306072255 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX name_domain');
        $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (name, domain_id, order_status_id)');
        $this->sql('ALTER TABLE mail_templates DROP COLUMN transport_id');
        $this->sql('ALTER TABLE mail_templates DROP COLUMN payment_id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
