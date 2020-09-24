<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;

use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200924135656 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->createOrderStatusWithEnglishAndCzechTranslations(6, 'IM - odesláno');
        $this->createOrderStatusWithEnglishAndCzechTranslations(7, 'IM - chyba');
        $this->createOrderStatusWithEnglishAndCzechTranslations(8, 'ERP - skladem');
        $this->createOrderStatusWithEnglishAndCzechTranslations(9, 'ERP - v převozu');
        $this->createOrderStatusWithEnglishAndCzechTranslations(10, 'ERP - čeká na dodání');
        $this->createOrderStatusWithEnglishAndCzechTranslations(11, 'ERP - Objednáno u dodavatele');
        $this->createOrderStatusWithEnglishAndCzechTranslations(12, 'ERP - chyba');
    }

    /**
    * @param int $orderStatusType
    * @param string $orderStatusCzechName
    */
    private function createOrderStatusWithEnglishAndCzechTranslations(
        $orderStatusType,
        $orderStatusCzechName
    ): void {
        $this->sql('INSERT INTO order_statuses (type) VALUES (:type)', [
            'type' => $orderStatusType,
        ]);
        $id = $this->connection->lastInsertId();
        foreach (['cs', 'en', 'sk'] as $locale) {
            $this->sql('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)', [
                'translatableId' => $id,
                'name' => $orderStatusCzechName,
                'locale' => $locale,
            ]);
        }
        $mailTemplateName = sprintf('order_status_%d', $id);
        foreach ([1, 2] as $domainId) {
            $this->createMailTemplateIfNotExist($mailTemplateName, $domainId, 'false');
        }
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * @param string $mailTemplateName
     * @param int $domainId
     * @param string $sendMail
     */
    private function createMailTemplateIfNotExist($mailTemplateName, $domainId, $sendMail): void
    {
        $mailTemplateCount = $this->sql('SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName AND domain_id = :domainId', [
            'mailTemplateName' => $mailTemplateName,
            'domainId' => $domainId
        ])->fetchColumn(0);

        if ($mailTemplateCount <= 0) {
            $this->sql('INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)', [
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
                'sendMail' => $sendMail,
            ]);
        }
    }
}
