<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180603135340 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->createGetDomainIdsByLocaleFunction();
        $this->createGetDomainLocaleFunction();
        $this->createImmutableUnaccentFunction();
        $this->createNormalizeFunction();
        $this->createDefaultDbIndexes();
        $this->createProductCatnumTrigger();
        $this->createProductPartnoTrigger();
        $this->createProductTranslationNameTrigger();
        $this->createProductDomainDescriptionTrigger();
        $this->createProductDomainFulltextTriggerOnProduct();
        $this->createProductDomainFulltextTriggerOnProductTranslation();
        $this->createProductDomainFulltextTriggerOnProductDomain();
    }

    public function down(Schema $schema)
    {
    }

    private function createGetDomainIdsByLocaleFunction(): void
    {
        $this->sql('CREATE OR REPLACE FUNCTION get_domain_ids_by_locale(locale text) RETURNS SETOF integer AS $$
            BEGIN
                CASE
                    WHEN locale = \'en\' THEN RETURN NEXT 1;
                    ELSE RAISE EXCEPTION \'Locale % does not exists\', locale;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;');
    }

    private function createGetDomainLocaleFunction(): void
    {
        $this->sql('CREATE OR REPLACE FUNCTION get_domain_locale(domain_id integer) RETURNS text AS $$
            BEGIN
                CASE
                    WHEN domain_id = 1 THEN RETURN \'en\';
                    ELSE RAISE EXCEPTION \'Domain with ID % does not exists\', domain_id;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;');
    }

    private function createImmutableUnaccentFunction(): void
    {
        $this->sql('CREATE OR REPLACE FUNCTION immutable_unaccent(text)
            RETURNS text AS
            $$
            SELECT pg_catalog.unaccent(\'pg_catalog.unaccent\', $1)
            $$
            LANGUAGE SQL IMMUTABLE');
    }

    private function createNormalizeFunction(): void
    {
        $this->sql('CREATE OR REPLACE FUNCTION normalize(text)
            RETURNS text AS
            $$
            SELECT pg_catalog.lower(public.immutable_unaccent($1))
            $$
            LANGUAGE SQL IMMUTABLE');
    }

    private function createDefaultDbIndexes(): void
    {
        $this->sql('CREATE INDEX IF NOT EXISTS product_translations_name_normalize_idx
            ON product_translations (NORMALIZE(name))');
        $this->sql('CREATE INDEX IF NOT EXISTS product_catnum_normalize_idx
            ON products (NORMALIZE(catnum))');
        $this->sql('CREATE INDEX IF NOT EXISTS product_partno_normalize_idx
            ON products (NORMALIZE(partno))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_email_normalize_idx
            ON orders (NORMALIZE(email))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_last_name_normalize_idx
            ON orders (NORMALIZE(last_name))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_company_name_normalize_idx
            ON orders (NORMALIZE(company_name))');
        $this->sql('CREATE INDEX IF NOT EXISTS user_email_normalize_idx
            ON users (NORMALIZE(email))');
    }

    private function createProductCatnumTrigger(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_product_catnum_tsvector() RETURNS trigger AS $$
                BEGIN
                    NEW.catnum_tsvector := to_tsvector(coalesce(NEW.catnum, \'\'));
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_catnum_tsvector ON products');
        $this->sql('
            CREATE TRIGGER recalc_catnum_tsvector
            BEFORE INSERT OR UPDATE OF catnum
            ON products
            FOR EACH ROW
            EXECUTE PROCEDURE set_product_catnum_tsvector();
        ');
    }

    private function createProductPartnoTrigger(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_product_partno_tsvector() RETURNS trigger AS $$
                BEGIN
                    NEW.partno_tsvector := to_tsvector(coalesce(NEW.partno, \'\'));
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_partno_tsvector ON products');
        $this->sql('
            CREATE TRIGGER recalc_partno_tsvector
            BEFORE INSERT OR UPDATE OF partno
            ON products
            FOR EACH ROW
            EXECUTE PROCEDURE set_product_partno_tsvector();
        ');
    }

    private function createProductTranslationNameTrigger(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_product_translation_name_tsvector() RETURNS trigger AS $$
                BEGIN
                    NEW.name_tsvector := to_tsvector(coalesce(NEW.name, \'\'));
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_name_tsvector ON product_translations');
        $this->sql('
            CREATE TRIGGER recalc_name_tsvector
            BEFORE INSERT OR UPDATE OF name
            ON product_translations
            FOR EACH ROW
            EXECUTE PROCEDURE set_product_translation_name_tsvector();
        ');
    }

    private function createProductDomainDescriptionTrigger(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_product_domain_description_tsvector() RETURNS trigger AS $$
                BEGIN
                    NEW.description_tsvector := to_tsvector(coalesce(NEW.description, \'\'));
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_description_tsvector ON product_domains');
        $this->sql('
            CREATE TRIGGER recalc_description_tsvector
            BEFORE INSERT OR UPDATE OF description
            ON product_domains
            FOR EACH ROW
            EXECUTE PROCEDURE set_product_domain_description_tsvector();
        ');
    }

    private function createProductDomainFulltextTriggerOnProduct(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION update_product_domain_fulltext_tsvector_by_product() RETURNS trigger AS $$
                BEGIN
                    UPDATE product_domains pd
                        SET fulltext_tsvector =
                            (
                                to_tsvector(COALESCE(pt.name, \'\'))
                                ||
                                to_tsvector(COALESCE(NEW.catnum, \'\'))
                                ||
                                to_tsvector(COALESCE(NEW.partno, \'\'))
                                ||
                                to_tsvector(COALESCE(pd.description, \'\'))
                                ||
                                to_tsvector(COALESCE(pd.short_description, \'\'))
                            )
                    FROM product_translations pt
                    WHERE pt.translatable_id = NEW.id
                        AND pt.locale = get_domain_locale(pd.domain_id)
                        AND pd.product_id = NEW.id;
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_product_domain_fulltext_tsvector ON products');
        $this->sql('
            CREATE TRIGGER recalc_product_domain_fulltext_tsvector
            AFTER INSERT OR UPDATE OF catnum, partno
            ON products
            FOR EACH ROW
            EXECUTE PROCEDURE update_product_domain_fulltext_tsvector_by_product();
        ');
    }

    private function createProductDomainFulltextTriggerOnProductTranslation(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION update_product_domain_fulltext_tsvector_by_product_translation() RETURNS trigger AS $$
                BEGIN
                    UPDATE product_domains pd
                        SET fulltext_tsvector =
                            (
                                to_tsvector(COALESCE(NEW.name, \'\'))
                                ||
                                to_tsvector(COALESCE(p.catnum, \'\'))
                                ||
                                to_tsvector(COALESCE(p.partno, \'\'))
                                ||
                                to_tsvector(COALESCE(pd.description, \'\'))
                                ||
                                to_tsvector(COALESCE(pd.short_description, \'\'))
                            )
                    FROM products p
                    WHERE p.id = NEW.translatable_id
                        AND pd.product_id = NEW.translatable_id
                        AND pd.domain_id IN (SELECT * FROM get_domain_ids_by_locale(NEW.locale));
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS recalc_product_domain_fulltext_tsvector ON product_translations');
        $this->sql('
            CREATE TRIGGER recalc_product_domain_fulltext_tsvector
            AFTER INSERT OR UPDATE OF name
            ON product_translations
            FOR EACH ROW
            EXECUTE PROCEDURE update_product_domain_fulltext_tsvector_by_product_translation();
        ');
    }

    private function createProductDomainFulltextTriggerOnProductDomain(): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_product_domain_fulltext_tsvector() RETURNS trigger AS $$
                BEGIN
                    NEW.fulltext_tsvector :=
                        (
                            SELECT
                                to_tsvector(COALESCE(pt.name, \'\'))
                                ||
                                to_tsvector(COALESCE(p.catnum, \'\'))
                                ||
                                to_tsvector(COALESCE(p.partno, \'\'))
                                ||
                                to_tsvector(COALESCE(NEW.description, \'\'))
                                ||
                                to_tsvector(COALESCE(NEW.short_description, \'\'))
                            FROM products p
                            LEFT JOIN product_translations pt ON pt.translatable_id = p.id
                                AND pt.locale = get_domain_locale(NEW.domain_id)
                            WHERE p.id = NEW.product_id
                        );

                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('
            DROP TRIGGER IF EXISTS recalc_product_domain_fulltext_tsvector on product_domains;
        ');
        $this->sql('
            CREATE TRIGGER recalc_product_domain_fulltext_tsvector
            BEFORE INSERT OR UPDATE OF description, short_description
            ON product_domains
            FOR EACH ROW
            EXECUTE PROCEDURE set_product_domain_fulltext_tsvector();
        ');
    }
}
