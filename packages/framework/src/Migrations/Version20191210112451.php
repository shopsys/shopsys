<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191210112451 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files ADD slug VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE uploaded_files ADD name VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->sql('UPDATE uploaded_files SET name = id, slug = id');
        $this->sql('ALTER TABLE uploaded_files ALTER slug DROP DEFAULT');
        $this->sql('ALTER TABLE uploaded_files ALTER name DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
