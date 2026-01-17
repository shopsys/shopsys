<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240403095810 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE tranfers rename to transfers');
        $this->sql('ALTER INDEX uniq_57310e34772e836a RENAME TO UNIQ_802A3918772E836A;');
        $this->sql('ALTER SEQUENCE tranfers_id_seq RENAME TO transfers_id_seq');
    }
}
