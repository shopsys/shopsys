<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250119100000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $primaryLocale = $this->getDomainLocale($this->getAllDomainIds()[0]);
        $pageName = t('Catalog', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $primaryLocale);

        $seoPageId = $this->getExistingSeoPageId($pageName);

        if ($seoPageId === null) {
            $this->sql('INSERT INTO seo_pages (page_name, default_page) VALUES (:pageName, true)', [
                'pageName' => $pageName,
            ]);
            $seoPageId = (int)$this->connection->lastInsertId();
        }

        foreach ($this->getAllDomainIds() as $domainId) {
            $locale = $this->getDomainLocale($domainId);
            $pageSlug = t('catalog-slug', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->insertSeoPageDomainIfNotExists($seoPageId, $domainId, $pageSlug);
            $this->insertFriendlyUrlIfNotExists($seoPageId, $domainId, $pageSlug);
        }
    }

    private function getExistingSeoPageId(string $pageName): ?int
    {
        /** @var int|string|false $seoPageId */
        $seoPageId = $this->sqlQuery(
            'SELECT id FROM seo_pages WHERE page_name = :pageName',
            ['pageName' => $pageName],
        )->fetchOne();

        return $seoPageId === false ? null : (int)$seoPageId;
    }

    private function insertSeoPageDomainIfNotExists(int $seoPageId, int $domainId, string $pageSlug): void
    {
        /** @var int|string|false $existingId */
        $existingId = $this->sqlQuery(
            'SELECT id FROM seo_page_domains WHERE seo_page_id = :seoPageId AND domain_id = :domainId',
            ['seoPageId' => $seoPageId, 'domainId' => $domainId],
        )->fetchOne();

        if ($existingId !== false) {
            return;
        }

        $this->sql('
            INSERT INTO seo_page_domains (
                seo_page_id,
                domain_id,
                page_slug,
                seo_title,
                seo_meta_description,
                canonical_url,
                seo_og_title,
                seo_og_description
            )
            VALUES (:seoPageId, :domainId, :pageSlug, null, null, null, null, null)
        ', [
            'seoPageId' => $seoPageId,
            'domainId' => $domainId,
            'pageSlug' => $pageSlug,
        ]);
    }

    private function insertFriendlyUrlIfNotExists(int $seoPageId, int $domainId, string $pageSlug): void
    {
        /** @var array{route_name: string, entity_id: int|string}|false $existingFriendlyUrl */
        $existingFriendlyUrl = $this->sqlQuery(
            'SELECT route_name, entity_id FROM friendly_urls WHERE domain_id = :domainId AND slug = :pageSlug',
            ['domainId' => $domainId, 'pageSlug' => $pageSlug],
        )->fetchAssociative();

        if ($existingFriendlyUrl !== false) {
            return;
        }

        $this->sql('
            INSERT INTO friendly_urls (
                domain_id,
                slug,
                route_name,
                entity_id,
                main,
                redirect_to,
                redirect_code,
                last_modification
            )
            VALUES (:domainId, :pageSlug, :routeName, :entityId, true, null, null, null)
        ', [
            'domainId' => $domainId,
            'pageSlug' => $pageSlug,
            'routeName' => 'front_page_seo',
            'entityId' => $seoPageId,
        ]);
    }
}
