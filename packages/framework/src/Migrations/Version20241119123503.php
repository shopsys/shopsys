<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241119123503 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE seo_page_domains ADD page_slug TEXT NOT NULL DEFAULT \'\'');

        $this->sql('
            UPDATE seo_page_domains
                SET page_slug = friendly_urls.slug
            FROM friendly_urls
            WHERE friendly_urls.entity_id = seo_page_domains.seo_page_id
                AND friendly_urls.domain_id = seo_page_domains.domain_id
                AND friendly_urls.route_name = \'front_page_seo\'
                AND friendly_urls.main = true
        ');

        $this->sql('DELETE FROM friendly_urls WHERE route_name = \'front_page_seo\' AND friendly_urls.main = true');

        $this->sql('ALTER TABLE seo_page_domains ALTER page_slug DROP DEFAULT');
    }
}
