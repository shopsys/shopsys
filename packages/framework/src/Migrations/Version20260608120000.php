<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260608120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_statuses ADD code VARCHAR(255) DEFAULT NULL');
        $this->sql('UPDATE order_statuses SET code = \'new\' WHERE id = 1');
        $this->sql('UPDATE order_statuses SET code = \'in-progress\' WHERE id = 2');
        $this->sql('UPDATE order_statuses SET code = \'done\' WHERE id = 3');
        $this->sql('UPDATE order_statuses SET code = \'canceled\' WHERE id = 4');
        $this->sql('UPDATE order_statuses SET code = \'withdrawn\' WHERE type = \'withdrawn\'');
        $this->sql('
            WITH generated_codes AS (
                SELECT
                    os.id,
                    COALESCE(
                        NULLIF(TRIM(BOTH \'-\' FROM REGEXP_REPLACE(LOWER(ost.name), \'[^a-z0-9]+\', \'-\', \'g\')), \'\'),
                        CONCAT(\'order-status-\', os.id)
                    ) AS base_code
                FROM order_statuses os
                JOIN order_status_translations ost ON ost.id = (
                    SELECT MIN(ost2.id)
                    FROM order_status_translations ost2
                    WHERE ost2.translatable_id = os.id
                )
                WHERE os.code IS NULL
            ),
            unique_codes AS (
                SELECT
                    id,
                    CASE
                        WHEN ROW_NUMBER() OVER (PARTITION BY base_code ORDER BY id) = 1 THEN base_code
                        ELSE CONCAT(base_code, \'-\', ROW_NUMBER() OVER (PARTITION BY base_code ORDER BY id))
                    END AS code
                FROM generated_codes
            )
            UPDATE order_statuses os
            SET code = unique_codes.code
            FROM unique_codes
            WHERE unique_codes.id = os.id
        ');
        $this->sql('UPDATE order_statuses SET code = CONCAT(\'order-status-\', id) WHERE code IS NULL OR code = \'\'');
        $this->sql('ALTER TABLE order_statuses ALTER code SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_AA08FCA077153098 ON order_statuses (code)');

        $this->sql('ALTER TABLE complaint_statuses ADD code VARCHAR(255) DEFAULT NULL');
        $this->sql('UPDATE complaint_statuses SET code = \'new\' WHERE id = 1');
        $this->sql('UPDATE complaint_statuses SET code = \'resolved\' WHERE id = 2');
        $this->sql('UPDATE complaint_statuses SET code = \'in-progress\' WHERE id = 3');
        $this->sql('
            WITH generated_codes AS (
                SELECT
                    cs.id,
                    COALESCE(
                        NULLIF(TRIM(BOTH \'-\' FROM REGEXP_REPLACE(LOWER(cst.name), \'[^a-z0-9]+\', \'-\', \'g\')), \'\'),
                        CONCAT(\'complaint-status-\', cs.id)
                    ) AS base_code
                FROM complaint_statuses cs
                JOIN complaint_status_translations cst ON cst.id = (
                    SELECT MIN(cst2.id)
                    FROM complaint_status_translations cst2
                    WHERE cst2.translatable_id = cs.id
                )
                WHERE cs.code IS NULL
            ),
            unique_codes AS (
                SELECT
                    id,
                    CASE
                        WHEN ROW_NUMBER() OVER (PARTITION BY base_code ORDER BY id) = 1 THEN base_code
                        ELSE CONCAT(base_code, \'-\', ROW_NUMBER() OVER (PARTITION BY base_code ORDER BY id))
                    END AS code
                FROM generated_codes
            )
            UPDATE complaint_statuses cs
            SET code = unique_codes.code
            FROM unique_codes
            WHERE unique_codes.id = cs.id
        ');
        $this->sql('UPDATE complaint_statuses SET code = CONCAT(\'complaint-status-\', id) WHERE code IS NULL OR code = \'\'');
        $this->sql('ALTER TABLE complaint_statuses ALTER code SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_57E9379377153098 ON complaint_statuses (code)');
    }
}
