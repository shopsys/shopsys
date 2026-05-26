<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200310130000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transports ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE transports SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE transports ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_C7BE69E5D17F50A6 ON transports (uuid)');
    }
}
