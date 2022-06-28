<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220628185551 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE ready_category_seo_mixes ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE ready_category_seo_mixes SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE ready_category_seo_mixes ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_74803E8FD17F50A6 ON ready_category_seo_mixes (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
