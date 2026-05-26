<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240911133739 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transports ADD type VARCHAR(25) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE transports ALTER type DROP DEFAULT');

        $this->sql(
            'UPDATE transports SET type = (SELECT code FROM transport_types WHERE transports.transport_type_id = transport_types.id)',
        );
        $this->sql('ALTER TABLE transports DROP COLUMN transport_type_id');

        $this->sql('DROP TABLE transport_type_translations');
        $this->sql('DROP TABLE transport_types');
    }
}
