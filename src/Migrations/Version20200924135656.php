<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200924135656 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->createOrderStatusWithEnglishAndCzechTranslations(5, 'Overlimit', 'Nadlimitní');
        $this->createOrderStatusWithEnglishAndCzechTranslations(6, 'IM - sent', 'IM - odesláno');
        $this->createOrderStatusWithEnglishAndCzechTranslations(7, 'IM - error', 'IM - chyba');
        $this->createOrderStatusWithEnglishAndCzechTranslations(8, 'ERP - in stock', 'ERP - skladem');
        $this->createOrderStatusWithEnglishAndCzechTranslations(9, 'ERP - in transit', 'ERP - v převozu');
        $this->createOrderStatusWithEnglishAndCzechTranslations(10, 'ERP - waiting', 'ERP - čeká na dodání');
        $this->createOrderStatusWithEnglishAndCzechTranslations(11, 'ERP - ordered', 'ERP - Objednáno u dodavatele');
        $this->createOrderStatusWithEnglishAndCzechTranslations(12, 'ERP - error', 'ERP - chyba');
        $this->sql('ALTER SEQUENCE order_statuses_id_seq RESTART WITH 13');
    }

    /**
    * @param int $orderStatusType
    * @param string $orderStatusEnglishName
    * @param string $orderStatusCzechName
    */
    private function createOrderStatusWithEnglishAndCzechTranslations(
        $orderStatusType,
        $orderStatusEnglishName,
        $orderStatusCzechName
    ) {
        $this->sql('INSERT INTO order_statuses (type) VALUES (:type)', [
            'type' => $orderStatusType,
        ]);
        $id = $this->connection->lastInsertId();
        $this->sql('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)', [
            'translatableId' => $id,
            'name' => $orderStatusEnglishName,
            'locale' => 'en',
        ]);
        $this->sql('INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)', [
            'translatableId' => $id,
            'name' => $orderStatusCzechName,
            'locale' => 'cs',
        ]);
    }

    public function down(Schema $schema) : void
    {
    }
}
