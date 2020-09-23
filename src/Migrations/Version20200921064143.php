<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200921064143 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE friendly_urls SET slug = CONCAT(\'magazin/\', slug) WHERE "route_name" = \'front_blogcategory_detail\' AND "slug" NOT LIKE \'magazin%\' AND "slug" !=\'blog\'');
        $this->sql('UPDATE friendly_urls SET slug = CONCAT(\'magazin/\', slug) WHERE "route_name" = \'front_blogarticle_detail\' AND "slug" NOT LIKE \'magazin%\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
