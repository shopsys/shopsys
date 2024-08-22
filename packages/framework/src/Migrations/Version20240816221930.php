<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240816221930 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE complaint_statuses (id SERIAL NOT NULL, status_type VARCHAR(25) NOT NULL, PRIMARY KEY(id))');
        $this->sql('
            CREATE TABLE complaint_status_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_BBBBB6722C2AC5D3 ON complaint_status_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX complaint_status_translations_uniq_trans ON complaint_status_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                complaint_status_translations
            ADD
                CONSTRAINT FK_BBBBB6722C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES complaint_statuses (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->createComplaintStatusWithEnglishAndCzechTranslations(
            1,
            ComplaintStatusTypeEnum::STATUS_TYPE_NEW,
            'New',
            'Nová',
        );
        $this->createComplaintStatusWithEnglishAndCzechTranslations(
            2,
            'resolved',
            'Resolved',
            'Vyřízena',
        );

        $this->sql('ALTER SEQUENCE complaint_statuses_id_seq RESTART WITH 3');

        $this->sql('ALTER TABLE complaints ADD status_id INT NULL');
        $this->sql('
            ALTER TABLE
                complaints
            ADD
                CONSTRAINT FK_A05AAF3A6BF700BD FOREIGN KEY (status_id) REFERENCES complaint_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_A05AAF3A6BF700BD ON complaints (status_id)');

        $this->sql('UPDATE complaints SET status_id = 1 WHERE status = \'new\'');
        $this->sql('UPDATE complaints SET status_id = 2 WHERE status = \'resolved\'');

        $this->sql('ALTER TABLE complaints DROP COLUMN status');
        $this->sql('ALTER TABLE complaints ALTER status_id SET NOT NULL');
    }

    /**
     * @param int $complaintStatusId
     * @param string $complaintStatus
     * @param string $complaintStatusEnglishName
     * @param string $complaintStatusCzechName
     */
    private function createComplaintStatusWithEnglishAndCzechTranslations(
        int $complaintStatusId,
        string $complaintStatus,
        string $complaintStatusEnglishName,
        string $complaintStatusCzechName,
    ): void {
        $this->sql('INSERT INTO complaint_statuses (id, status_type) VALUES (:id, :statusType)', [
            'id' => $complaintStatusId,
            'statusType' => $complaintStatus,
        ]);
        $this->sql(
            'INSERT INTO complaint_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)',
            [
                'translatableId' => $complaintStatusId,
                'name' => $complaintStatusEnglishName,
                'locale' => 'en',
            ],
        );
        $this->sql(
            'INSERT INTO complaint_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)',
            [
                'translatableId' => $complaintStatusId,
                'name' => $complaintStatusCzechName,
                'locale' => 'cs',
            ],
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
