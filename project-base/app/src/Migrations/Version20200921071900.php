<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200921071900 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO order_statuses (type) VALUES (5)');
        $lastOrderStatusId = $this->connection->lastInsertId('order_statuses_id_seq');

        $this->sql(sprintf('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (%d, \'Nadlimitní\', \'cs\')', $lastOrderStatusId));
        $this->sql(sprintf('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (%d, \'Over limit\', \'en\')', $lastOrderStatusId));

        $this->sql('ALTER TABLE orders ADD is_over_limit BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE orders ALTER is_over_limit DROP DEFAULT');

        $this->createMailTemplateIfNotExist('order_status_5', 1, 'false');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param string $mailTemplateName
     * @param int $domainId
     * @param string $sendMail
     */
    private function createMailTemplateIfNotExist($mailTemplateName, $domainId, $sendMail)
    {
        $mailTemplateCount = $this->sql('SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName AND domain_id = :domainId', [
            'mailTemplateName' => $mailTemplateName,
            'domainId' => $domainId,
        ])->fetchOne();

        if ($mailTemplateCount <= 0) {
            $this->sql('INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)', [
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
                'sendMail' => $sendMail,
            ]);
        }
    }
}
