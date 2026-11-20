<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261120120000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $pageSlugs = [];
        $existingPageIds = [];

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $pageSlug = in_array($domainConfig->getLocale(), ['cs', 'sk'], true) ? 'zakaznik' : 'customer';
            $pageSlugs[$domainId] = $pageSlug;

            $existingPageId = $this->sqlQuery(
                'SELECT seo_page_id FROM seo_page_domains WHERE domain_id = :domainId AND page_slug = :pageSlug',
                ['domainId' => $domainId, 'pageSlug' => $pageSlug],
            )->fetchOne();

            if ($existingPageId !== false) {
                $existingPageIds[$domainId] = (int)$existingPageId;
            }
        }

        if ($existingPageIds === []) {
            $this->sql('INSERT INTO seo_pages (page_name, default_page) VALUES (:pageName, true)', [
                'pageName' => 'Zákazník - přehled účtu',
            ]);
            $seoPageId = (int)$this->connection->lastInsertId();
        } else {
            $seoPageId = reset($existingPageIds);
        }

        foreach ($pageSlugs as $domainId => $pageSlug) {
            $this->sql('
                INSERT INTO seo_page_domains (seo_page_id, domain_id, page_slug, seo_meta_robots)
                VALUES (:seoPageId, :domainId, :pageSlug, :robots)
                ON CONFLICT (seo_page_id, domain_id) DO UPDATE
                SET seo_meta_robots = EXCLUDED.seo_meta_robots
                WHERE seo_page_domains.page_slug = EXCLUDED.page_slug
            ', [
                'seoPageId' => $existingPageIds[$domainId] ?? $seoPageId,
                'domainId' => $domainId,
                'pageSlug' => $pageSlug,
                'robots' => SeoMetaRobotsEnum::NOINDEX,
            ]);
        }
    }
}
