<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210709155038 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE blog_categories ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE blog_categories SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE blog_categories ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DC356481D17F50A6 ON blog_categories (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
