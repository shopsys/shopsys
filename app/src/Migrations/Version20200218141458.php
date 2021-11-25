<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200218141458 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE articles ADD external BOOLEAN NOT NULL DEFAULT false');
        $this->sql('ALTER TABLE articles ALTER external DROP DEFAULT');

        $this->sql('ALTER TABLE articles ADD type VARCHAR(255) NOT NULL DEFAULT \'site\'');
        $this->sql('ALTER TABLE articles ALTER type DROP DEFAULT');

        $this->sql('ALTER TABLE articles ADD url VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE articles ALTER url DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
