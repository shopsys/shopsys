<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240909162017 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE customer_uploaded_files SET hash = MD5(RANDOM()::text) WHERE hash IS NULL');
        $this->sql('ALTER TABLE customer_uploaded_files ALTER hash SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_uploaded_files ALTER hash DROP NOT NULL');
    }
}
