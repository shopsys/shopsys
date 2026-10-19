<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261019120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->renameGridId('advertList', 'Advert');
        $this->renameGridId('priceList', 'PriceList');
    }

    private function renameGridId(string $legacyGridId, string $crudGridId): void
    {
        $this->sql(
            'UPDATE administrator_grid_limits AS legacy_limit
            SET grid_id = :crudGridId
            WHERE legacy_limit.grid_id = :legacyGridId
                AND NOT EXISTS (
                    SELECT 1
                    FROM administrator_grid_limits AS crud_limit
                    WHERE crud_limit.administrator_id = legacy_limit.administrator_id
                        AND crud_limit.grid_id = :crudGridId
                )',
            [
                'crudGridId' => $crudGridId,
                'legacyGridId' => $legacyGridId,
            ],
        );

        $this->sql(
            'DELETE FROM administrator_grid_limits WHERE grid_id = :legacyGridId',
            [
                'legacyGridId' => $legacyGridId,
            ],
        );
    }
}
