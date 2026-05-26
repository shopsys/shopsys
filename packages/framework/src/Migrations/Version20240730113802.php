<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240730113802 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'INSERT INTO setting_values (name, domain_id, value, type) VALUES (?, 0, \'false\', \'boolean\')',
            [Setting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS],
        );
    }
}
