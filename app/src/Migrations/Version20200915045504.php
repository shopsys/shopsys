<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200915045504 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX name_domain');
        $this->sql('ALTER TABLE mail_templates ADD transport_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE mail_templates ADD payment_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE mail_templates ADD order_stock_status VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE mail_templates
            ADD CONSTRAINT FK_17F263ED9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE mail_templates
            ADD CONSTRAINT FK_17F263ED4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_17F263ED9909C13F ON mail_templates (transport_id)');
        $this->sql('CREATE INDEX IDX_17F263ED4C3A3BB ON mail_templates (payment_id)');
        $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (
                name, domain_id, transport_id, payment_id, order_stock_status
            )');
        $this->sql('ALTER TABLE orders ADD stock_status VARCHAR(32) DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
