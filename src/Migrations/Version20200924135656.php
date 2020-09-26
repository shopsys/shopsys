<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200924135656 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->createOrderStatusWithEnglishAndCzechTranslations(5, 2, 'Overlimit', 'Nadlimitní');
        $this->createOrderStatusWithEnglishAndCzechTranslations(6, 2, 'IM - sent', 'IM - odesláno');
        $this->createOrderStatusWithEnglishAndCzechTranslations(7, 2, 'IM - error', 'IM - chyba');
        $this->createOrderStatusWithEnglishAndCzechTranslations(8, 2, 'ERP - in stock', 'ERP - skladem');
        $this->createOrderStatusWithEnglishAndCzechTranslations(9, 2, 'ERP - in transit', 'ERP - v převozu');
        $this->createOrderStatusWithEnglishAndCzechTranslations(10, 2, 'ERP - waiting', 'ERP - čeká na dodání');
        $this->createOrderStatusWithEnglishAndCzechTranslations(11, 2, 'ERP - ordered', 'ERP - Objednáno u dodavatele');
        $this->createOrderStatusWithEnglishAndCzechTranslations(12, 2, 'ERP - error', 'ERP - chyba');
        $this->sql('ALTER SEQUENCE order_statuses_id_seq RESTART WITH 13');
    }

    /**
    * @param int $orderStatusId
    * @param int $orderStatusType
    * @param string $orderStatusEnglishName
    * @param string $orderStatusCzechName
    */
    private function createOrderStatusWithEnglishAndCzechTranslations(
        $orderStatusId,
        $orderStatusType,
        $orderStatusEnglishName,
        $orderStatusCzechName
    ) {
        $this->sql('INSERT INTO order_statuses (id, type) VALUES (:id, :type)', [
            'id' => $orderStatusId,
            'type' => $orderStatusType,
        ]);
        $this->sql('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)', [
            'translatableId' => $orderStatusId,
            'name' => $orderStatusEnglishName,
            'locale' => 'en',
        ]);
        $this->sql('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)', [
            'translatableId' => $orderStatusId,
            'name' => $orderStatusCzechName,
            'locale' => 'cs',
        ]);
    }

    public function down(Schema $schema) : void
    {
    }
}
