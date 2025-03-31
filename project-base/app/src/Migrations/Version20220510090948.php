<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220510090948 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM friendly_urls
            USING products
            WHERE friendly_urls.entity_id = products.id
            AND friendly_urls.route_name = \'front_product_detail\'
            AND friendly_urls.slug = \'article/\' || products.catnum
        ');
    }
}
