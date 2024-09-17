<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240826114720 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE mail_templates ADD complaint_status_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                mail_templates
            ADD
                CONSTRAINT FK_17F263ED685DFB98 FOREIGN KEY (complaint_status_id) REFERENCES complaint_statuses (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_17F263ED685DFB98 ON mail_templates (complaint_status_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
