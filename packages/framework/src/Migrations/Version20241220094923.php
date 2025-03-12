<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241220094923 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE UNIQUE INDEX UNIQ_73A716FE7927C74 ON administrators (email)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function down(Schema $schema): void
    {
    }
}
