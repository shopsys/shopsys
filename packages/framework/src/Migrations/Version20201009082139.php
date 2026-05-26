<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20201009082139 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE adverts ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE adverts SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE adverts ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_8C88E777D17F50A6 ON adverts (uuid)');
    }
}
