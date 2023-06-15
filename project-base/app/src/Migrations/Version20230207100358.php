<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Model\SeoPage\SeoPage;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230207100358 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    private const PREDEFINED_SEO_PAGES = [
        'Homepage' => [
            'cs' => SeoPage::SEO_PAGE_HOMEPAGE_SLUG,
            'sk' => SeoPage::SEO_PAGE_HOMEPAGE_SLUG,
        ],
        'Nákupní košík' => [
            'cs' => 'kosik',
            'sk' => 'kosik',
        ],
        'Napište nám' => [
            'cs' => 'kontakt',
            'sk' => 'kontakt',
        ],
        'Zapomenuté heslo' => [
            'cs' => 'zapomenute-heslo',
            'sk' => 'zapomenute-heslo',
        ],
        'Registrace' => [
            'cs' => 'registrace',
            'sk' => 'registracia',
        ],
        'Prodejny' => [
            'cs' => 'obchodni-domy',
            'sk' => 'obchodne-domy',
        ],
        'Značky' => [
            'cs' => 'prehled-znacek',
            'sk' => 'prehled-znacek',
        ],
        'Přihlášení' => [
            'cs' => 'prihlaseni',
            'sk' => 'prihlasenie',
        ],
        'Souhlas se soubory cookies' => [
            'cs' => 'souhlas-se-soubory-cookies',
            'sk' => 'souhlas-se-soubory-cookies',
        ],
    ];

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE seo_pages (id SERIAL NOT NULL, page_name TEXT NOT NULL, default_page BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->sql('
            CREATE TABLE seo_page_domains (
                id SERIAL NOT NULL,
                seo_page_id INT NOT NULL,
                domain_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                canonical_url TEXT DEFAULT NULL,
                seo_og_title TEXT DEFAULT NULL,
                seo_og_description TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_92D1FC2AA57F59AF ON seo_page_domains (seo_page_id)');
        $this->sql('CREATE UNIQUE INDEX seo_page_domain ON seo_page_domains (seo_page_id, domain_id)');
        $this->sql('
            ALTER TABLE
                seo_page_domains
            ADD
                CONSTRAINT FK_92D1FC2AA57F59AF FOREIGN KEY (seo_page_id) REFERENCES seo_pages (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->createSeoPages();
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function createSeoPages(): void
    {
        foreach (self::PREDEFINED_SEO_PAGES as $pageName => $pageSlugsIndexedByLocale) {
            $this->sql('INSERT INTO seo_pages (page_name, default_page) VALUES (:pageName, true)', [
                'pageName' => $pageName,
            ]);
            $seoPageId = $this->connection->lastInsertId('seo_pages_id_seq');

            $this->createSeoPageDomainsForSeoPage($seoPageId, $pageSlugsIndexedByLocale);
        }
    }

    /**
     * @param int|string $seoPageId
     * @param array $pageSlugsIndexedByLocale
     */
    private function createSeoPageDomainsForSeoPage(int|string $seoPageId, array $pageSlugsIndexedByLocale): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $locale = $this->getDomainLocale($domainId);
            $this->sql('
                    INSERT INTO seo_page_domains (
                        seo_page_id,
                        domain_id,
                        seo_title,
                        seo_meta_description,
                        canonical_url,
                        seo_og_title,
                        seo_og_description
                    )
                    VALUES (:seoPageId, :domainId, null, null, null, null, null)
                ', [
                'seoPageId' => $seoPageId,
                'domainId' => $domainId,
            ]);

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
                'pageSlug' => $pageSlugsIndexedByLocale[$locale] ?? $pageSlugsIndexedByLocale['cs'],
                'routeName' => 'front_page_seo',
                'entityId' => $seoPageId,
            ]);
        }
    }
}
