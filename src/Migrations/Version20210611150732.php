<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210611150732 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE blog_articles ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE blog_articles SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE blog_articles ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_CB80154FD17F50A6 ON blog_articles (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
