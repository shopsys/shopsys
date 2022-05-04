<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200923075659 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE brands ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE brands SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE brands ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_7EA24434D17F50A6 ON brands (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
