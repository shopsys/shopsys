<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200914092721 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE url_redirect (old_url TEXT NOT NULL, new_url TEXT NOT NULL, PRIMARY KEY(old_url))');
        $this->sql('CREATE UNIQUE INDEX UNIQ_A16CF0A2CD77C9B8 ON url_redirect (new_url)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
