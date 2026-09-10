<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

/**
 * Seeds a SEO page for every static storefront page, the slugs mirror project-base/storefront/config/routes.ts
 *
 * Slugs are looked up by the locale of each domain, a domain with a locale not listed here gets the English slugs.
 * A page is matched by its name or, when the name differs (e.g. the translated "Catalog"), by one of its slugs;
 * a matched page gets the current name, slugs and robots, a missing page is created.
 */
final class Version20260909110000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    private const string FALLBACK_LOCALE = 'en';

    private const array SEO_PAGES = [
        'Nákupní košík' => [
            'slugs' => [
                'en' => 'cart',
                'cs' => 'kosik',
                'sk' => 'kosik',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Napište nám' => [
            'slugs' => [
                'en' => 'contact-form',
                'cs' => 'kontaktni-formular',
                'sk' => 'kontaktny-formular',
            ],
            'metaRobots' => null,
        ],
        'Zapomenuté heslo' => [
            'slugs' => [
                'en' => 'reset-password',
                'cs' => 'zapomenute-heslo',
                'sk' => 'zabudnute-heslo',
            ],
            'metaRobots' => null,
        ],
        'Registrace' => [
            'slugs' => [
                'en' => 'registration',
                'cs' => 'registrace',
                'sk' => 'registracia',
            ],
            'metaRobots' => null,
        ],
        'Prodejny' => [
            'slugs' => [
                'en' => 'stores',
                'cs' => 'obchodni-domy',
                'sk' => 'obchodne-domy',
            ],
            'metaRobots' => null,
        ],
        'Značky' => [
            'slugs' => [
                'en' => 'brands-overview',
                'cs' => 'prehled-znacek',
                'sk' => 'prehlad-znaciek',
            ],
            'metaRobots' => null,
        ],
        'Přihlášení' => [
            'slugs' => [
                'en' => 'login',
                'cs' => 'prihlaseni',
                'sk' => 'prihlasenie',
            ],
            'metaRobots' => null,
        ],
        'Souhlas se soubory cookies' => [
            'slugs' => [
                'en' => 'user-consent',
                'cs' => 'uzivatelsky-souhlas',
                'sk' => 'pouzivatelsky-suhlas',
            ],
            'metaRobots' => null,
        ],
        'Katalog' => [
            'slugs' => [
                'en' => 'catalog',
                'cs' => 'katalog',
                'sk' => 'katalog',
            ],
            'metaRobots' => null,
        ],
        'Vyhledávání' => [
            'slugs' => [
                'en' => 'search',
                'cs' => 'hledani',
                'sk' => 'hladanie',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX_NOFOLLOW,
        ],
        'Nové heslo' => [
            'slugs' => [
                'en' => 'new-password',
                'cs' => 'nove-heslo',
                'sk' => 'nove-heslo',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Oblíbené produkty' => [
            'slugs' => [
                'en' => 'wishlist',
                'cs' => 'oblibene-produkty',
                'sk' => 'oblubene-produkty',
            ],
            'metaRobots' => null,
        ],
        'Porovnání produktů' => [
            'slugs' => [
                'en' => 'product-comparison',
                'cs' => 'porovnani-produktu',
                'sk' => 'porovnanie-produktov',
            ],
            'metaRobots' => null,
        ],
        'Export osobních údajů' => [
            'slugs' => [
                'en' => 'personal-data-export',
                'cs' => 'export-osobnich-udaju',
                'sk' => 'export-osobnych-udajov',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Přehled osobních údajů' => [
            'slugs' => [
                'en' => 'personal-data-overview',
                'cs' => 'prehled-osobnich-udaju',
                'sk' => 'prehlad-osobnych-udajov',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Objednávka - doprava a platba' => [
            'slugs' => [
                'en' => 'order/transport-and-payment',
                'cs' => 'objednavka/doprava-a-platba',
                'sk' => 'objednavka/doprava-a-platba',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Objednávka - kontaktní údaje' => [
            'slugs' => [
                'en' => 'order/contact-information',
                'cs' => 'objednavka/kontaktni-udaje',
                'sk' => 'objednavka/kontaktne-udaje',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Potvrzení objednávky' => [
            'slugs' => [
                'en' => 'order-confirmation',
                'cs' => 'potvrzeni-objednavky',
                'sk' => 'potvrdenie-objednavky',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - objednávky' => [
            'slugs' => [
                'en' => 'customer/orders',
                'cs' => 'zakaznik/objednavky',
                'sk' => 'zakaznik/objednavky',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - detail objednávky' => [
            'slugs' => [
                'en' => 'customer/order-detail',
                'cs' => 'zakaznik/detail-objednavky',
                'sk' => 'zakaznik/detail-objednavky',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - reklamace' => [
            'slugs' => [
                'en' => 'customer/complaints',
                'cs' => 'zakaznik/reklamace',
                'sk' => 'zakaznik/reklamacie',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - detail reklamace' => [
            'slugs' => [
                'en' => 'customer/complaint-detail',
                'cs' => 'zakaznik/detail-reklamace',
                'sk' => 'zakaznik/detail-reklamacie',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - nová reklamace' => [
            'slugs' => [
                'en' => 'customer/new-complaint',
                'cs' => 'zakaznik/nova-reklamace',
                'sk' => 'zakaznik/nova-reklamacia',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - úprava údajů' => [
            'slugs' => [
                'en' => 'customer/edit-profile',
                'cs' => 'zakaznik/upravit-udaje',
                'sk' => 'zakaznik/upravit-udaje',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - změna hesla' => [
            'slugs' => [
                'en' => 'customer/change-password',
                'cs' => 'zakaznik/zmenit-heslo',
                'sk' => 'zakaznik/zmenit-heslo',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
        'Zákazník - uživatelé' => [
            'slugs' => [
                'en' => 'customer/users',
                'cs' => 'zakaznik/uzivatele',
                'sk' => 'zakaznik/pouzivatelia',
            ],
            'metaRobots' => SeoMetaRobotsEnum::NOINDEX,
        ],
    ];

    #[Override]
    public function up(Schema $schema): void
    {
        foreach (self::SEO_PAGES as $pageName => $seoPage) {
            $seoPageId = $this->findSeoPageIdByName($pageName) ?? $this->findSeoPageIdBySlugs($seoPage['slugs']);

            if ($seoPageId === null) {
                $this->sql('INSERT INTO seo_pages (page_name, default_page) VALUES (:pageName, true)', ['pageName' => $pageName]);
                $seoPageId = (int)$this->connection->lastInsertId();
            } else {
                $this->sql('UPDATE seo_pages SET page_name = :pageName WHERE id = :id', ['pageName' => $pageName, 'id' => $seoPageId]);
            }

            foreach ($this->getAllDomainIds() as $domainId) {
                $pageSlug = $seoPage['slugs'][$this->getDomainLocale($domainId)] ?? $seoPage['slugs'][self::FALLBACK_LOCALE];

                $this->upsertSeoPageDomain($seoPageId, $domainId, $pageSlug, $seoPage['metaRobots']);
            }
        }
    }

    private function findSeoPageIdByName(string $pageName): ?int
    {
        /** @var int|string|false $seoPageId */
        $seoPageId = $this->sqlQuery(
            'SELECT id FROM seo_pages WHERE page_name = :pageName',
            ['pageName' => $pageName],
        )->fetchOne();

        return $seoPageId === false ? null : (int)$seoPageId;
    }

    /**
     * @param array<string, string> $slugsByLocale
     */
    private function findSeoPageIdBySlugs(array $slugsByLocale): ?int
    {
        /** @var int|string|false $seoPageId */
        $seoPageId = $this->sqlQuery(
            'SELECT seo_page_id FROM seo_page_domains WHERE page_slug IN (:pageSlugs) ORDER BY id LIMIT 1',
            ['pageSlugs' => array_values($slugsByLocale)],
            ['pageSlugs' => ArrayParameterType::STRING],
        )->fetchOne();

        return $seoPageId === false ? null : (int)$seoPageId;
    }

    private function upsertSeoPageDomain(int $seoPageId, int $domainId, string $pageSlug, ?string $metaRobots): void
    {
        $parameters = [
            'seoPageId' => $seoPageId,
            'domainId' => $domainId,
            'pageSlug' => $pageSlug,
            'metaRobots' => $metaRobots,
        ];

        /** @var int|string|false $seoPageDomainId */
        $seoPageDomainId = $this->sqlQuery(
            'SELECT id FROM seo_page_domains WHERE seo_page_id = :seoPageId AND domain_id = :domainId',
            ['seoPageId' => $seoPageId, 'domainId' => $domainId],
        )->fetchOne();

        if ($seoPageDomainId === false) {
            $this->sql(
                'INSERT INTO seo_page_domains (seo_page_id, domain_id, page_slug, seo_meta_robots) VALUES (:seoPageId, :domainId, :pageSlug, :metaRobots)',
                $parameters,
            );

            return;
        }

        $this->sql(
            'UPDATE seo_page_domains SET page_slug = :pageSlug, seo_meta_robots = :metaRobots WHERE seo_page_id = :seoPageId AND domain_id = :domainId',
            $parameters,
        );
    }
}
