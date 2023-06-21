<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200924163010 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM mail_templates WHERE order_stock_status IS NOT NULL');
        $this->sql('ALTER TABLE orders DROP stock_status');
        $this->sql('DROP INDEX name_domain');
        $this->sql('ALTER TABLE mail_templates DROP order_stock_status');
        $this->sql('ALTER TABLE mail_templates ADD order_status_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE mail_templates 
            ADD CONSTRAINT FK_17F263EDD7707B45 FOREIGN KEY (order_status_id) REFERENCES order_statuses (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_17F263EDD7707B45 ON mail_templates (order_status_id)');
        $this->sql('
            CREATE UNIQUE INDEX name_domain ON mail_templates (
                name, domain_id, transport_id, payment_id,
                order_status_id
            )');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
