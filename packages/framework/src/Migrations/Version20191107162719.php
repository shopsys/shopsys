<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191107162719 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE vats ADD domain_id INT NOT NULL DEFAULT 1;');
        $this->sql('ALTER TABLE vats ALTER domain_id DROP DEFAULT;');

        $this->sql('ALTER TABLE vats ADD tmp_original_id INT;');
        $this->sql('COMMENT ON COLUMN vats.tmp_original_id IS \'Temporary column for the needs of migrations\';');

        $this->migrateCurrentVats();
        $this->migrateReplaceWithColumnData();
        $this->migrateCurrentVatSetting();
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function migrateCurrentVats(): void
    {
        $currentVats = $this->sql(
            'SELECT id, name, percent, replace_with_id FROM vats WHERE domain_id = 1'
        )->fetchAllAssociative();

        foreach ($this->getAllDomainIds() as $domainId) {
            foreach ($currentVats as $currentVat) {
                if ($domainId === 1) {
                    $this->sql(
                        'UPDATE vats SET tmp_original_id = :tmpOriginalId WHERE id = :id',
                        [
                            'id' => $currentVat['id'],
                            'tmpOriginalId' => $currentVat['id'],
                        ]
                    );
                } else {
                    $this->sql(
                        'INSERT INTO vats (replace_with_id, name, percent, domain_id, tmp_original_id)
                            VALUES (:replaceWithId, :name, :percent, :domainId, :tmpOriginalId)',
                        [
                            'replaceWithId' => $currentVat['replace_with_id'],
                            'name' => $currentVat['name'],
                            'percent' => $currentVat['percent'],
                            'domainId' => $domainId,
                            'tmpOriginalId' => $currentVat['id'],
                        ]
                    );
                }
            }
        }
    }

    private function migrateReplaceWithColumnData(): void
    {
        $vatsForMigrateReplaceWithColumn = $this->sql(
            'SELECT id, replace_with_id, domain_id FROM vats WHERE replace_with_id is not null and domain_id > 1'
        )->fetchAllAssociative();

        foreach ($vatsForMigrateReplaceWithColumn as $vatForMigrateReplaceWithColumn) {
            $newVatId = $this
                ->sql('SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId', [
                    'tmpOriginalId' => $vatForMigrateReplaceWithColumn['replace_with_id'],
                    'domainId' => $vatForMigrateReplaceWithColumn['domain_id'],
                ])
                ->fetchOne();

            $this->sql('UPDATE vats SET replace_with_id = :newVatId WHERE id = :id', [
                'newVatId' => $newVatId,
                'id' => $vatForMigrateReplaceWithColumn['id'],
            ]);
        }
    }

    private function migrateCurrentVatSetting(): void
    {
        $currentDefaultVat = $this->sql(
            'SELECT value FROM setting_values WHERE name = \'defaultVatId\' AND domain_id = 0;'
        )->fetchOne();

        foreach ($this->getAllDomainIds() as $domainId) {
            $newVatId = $this
                ->sql('SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId', [
                    'tmpOriginalId' => $currentDefaultVat,
                    'domainId' => $domainId,
                ])
                ->fetchOne();

            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'defaultVatId\', :domainId, :vatId, \'string\');',
                [
                    'domainId' => $domainId,
                    'vatId' => $newVatId,
                ]
            );
        }
    }
}
