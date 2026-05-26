<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250320124625 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE stores SET latitude = CASE
            WHEN latitude ~ \'[0-9.]+[NnEe]\' THEN regexp_replace(latitude, \'[^0-9.-]\', \'\', \'g\')
            WHEN latitude ~ \'[0-9.]+[SsWw]\' THEN \'-\' || regexp_replace(latitude, \'[^0-9.-]\', \'\', \'g\')
            ELSE latitude
        END');
        $this->sql('UPDATE stores SET longitude = CASE
            WHEN longitude ~ \'[0-9.]+[NnEe]\' THEN regexp_replace(longitude, \'[^0-9.-]\', \'\', \'g\')
            WHEN longitude ~ \'[0-9.]+[SsWw]\' THEN \'-\' || regexp_replace(longitude, \'[^0-9.-]\', \'\', \'g\')
            ELSE longitude
        END');
        $this->sql('ALTER TABLE stores ALTER latitude TYPE NUMERIC(20, 10) USING latitude::NUMERIC(20,10)');
        $this->sql('ALTER TABLE stores ALTER longitude TYPE NUMERIC(20, 10) USING longitude::NUMERIC(20,10)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}
