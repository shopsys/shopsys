<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260102102137 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaint_items ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE complaint_items SET uuid = gen_random_uuid() WHERE uuid IS NULL');
        $this->sql('ALTER TABLE complaint_items ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_A0C592FBD17F50A6 ON complaint_items (uuid)');
    }
}
