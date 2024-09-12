<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240403091822 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    private const string TRANSPORT_TYPE_COMMON = 'common';
    private const string TRANSPORT_TYPE_PACKETERY = 'packetery';
    private const string TRANSPORT_TYPE_PERSONAL_PICKUP = 'personal_pickup';

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200109151504')) {
            $this->sql('ALTER TABLE promo_codes ADD domain_id INT NOT NULL DEFAULT 1');
            $this->sql('ALTER TABLE promo_codes ALTER domain_id DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200128112829')) {
            $this->sql('ALTER TABLE promo_codes DROP "code"');
            $this->sql('ALTER TABLE promo_codes ADD "code" text NOT NULL DEFAULT 1');
            $this->sql('ALTER TABLE promo_codes ALTER "code" DROP DEFAULT');
            $this->sql('CREATE UNIQUE INDEX domain_code_unique ON promo_codes (domain_id, code)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200203123245')) {
            $this->sql('ALTER TABLE promo_codes ADD datetime_valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->sql('ALTER TABLE promo_codes ADD datetime_valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200214081059')) {
            $this->sql('
            CREATE TABLE promo_code_categories (
                promo_code_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, category_id)
            )');
            $this->sql('CREATE INDEX IDX_66ECEA5F2FAE4625 ON promo_code_categories (promo_code_id)');
            $this->sql('CREATE INDEX IDX_66ECEA5F12469DE2 ON promo_code_categories (category_id)');
            $this->sql('
            CREATE TABLE promo_code_products (
                promo_code_id INT NOT NULL,
                product_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, product_id)
            )');
            $this->sql('CREATE INDEX IDX_C970A1712FAE4625 ON promo_code_products (promo_code_id)');
            $this->sql('CREATE INDEX IDX_C970A1714584665A ON promo_code_products (product_id)');
            $this->sql('
            ALTER TABLE
                promo_code_categories
            ADD
                CONSTRAINT FK_66ECEA5F2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_categories
            ADD
                CONSTRAINT FK_66ECEA5F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_products
            ADD
                CONSTRAINT FK_C970A1712FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_products
            ADD
                CONSTRAINT FK_C970A1714584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200218134211')) {
            $this->sql('ALTER TABLE promo_codes ADD remaining_uses INT DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200514134336')) {
            $this->sql('ALTER TABLE promo_codes ADD identifier TEXT NOT NULL DEFAULT \'XX\'');
            $this->sql('ALTER TABLE promo_codes ALTER identifier DROP DEFAULT');
            $this->sql('ALTER TABLE order_items ADD promo_code_identifier TEXT DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200522093142')) {
            $this->sql('ALTER TABLE promo_codes ADD apply_on_second_product BOOLEAN NOT NULL default FALSE');
            $this->sql('ALTER TABLE promo_codes ALTER apply_on_second_product drop default');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200616093504')) {
            $this->sql('ALTER TABLE promo_codes ADD on_sale BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE promo_codes ALTER on_sale DROP DEFAULT');
            $this->sql('ALTER TABLE promo_codes ADD in_action BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE promo_codes ALTER in_action DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200714114004')) {
            $this->sql('
            CREATE TABLE promo_code_limit (
                from_price_with_vat NUMERIC(20,4) NOT NULL,
                percent NUMERIC(20, 4) NOT NULL, 
                promo_code_id INT NOT NULL, 
                PRIMARY KEY(promo_code_id, from_price_with_vat)
            );
            ');
            $this->sql('CREATE INDEX IDX_CF58514F2FAE4625 ON promo_code_limit (promo_code_id)');
            $this->sql('ALTER TABLE promo_code_limit ADD CONSTRAINT FK_CF58514F2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200716132559')) {
            $this->sql('INSERT INTO promo_code_limit (from_price_with_vat, percent, promo_code_id) SELECT 1, percent, id FROM promo_codes;');
            $this->sql('ALTER TABLE "promo_codes" DROP "percent";');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200803152004')) {
            $this->sql('ALTER TABLE promo_codes ADD discount_type INT NOT NULL default 1;');
            $this->sql('ALTER TABLE promo_codes ALTER discount_type DROP DEFAULT;');
            $this->sql('ALTER TABLE promo_code_limit RENAME COLUMN percent TO discount;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200819070557')) {
            $this->sql('ALTER TABLE promo_codes ADD price_hit BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE promo_codes ALTER price_hit DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210504122949')) {
            $this->sql('
            CREATE TABLE promo_code_brands (
                promo_code_id INT NOT NULL,
                brand_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, brand_id)
            )');
            $this->sql('CREATE INDEX IDX_D5C3B7D42FAE4625 ON promo_code_brands (promo_code_id)');
            $this->sql('CREATE INDEX IDX_D5C3B7D444F5D008 ON promo_code_brands (brand_id)');
            $this->sql('
            ALTER TABLE
                promo_code_brands
            ADD
                CONSTRAINT FK_D5C3B7D42FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_brands
            ADD
                CONSTRAINT FK_D5C3B7D444F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210906123951')) {
            $this->sql('
            CREATE TABLE promo_code_pricing_groups (
                promo_code_id INT NOT NULL,
                pricing_group_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, pricing_group_id)
            )');
            $this->sql('CREATE INDEX IDX_78E70F132FAE4625 ON promo_code_pricing_groups (promo_code_id)');
            $this->sql('CREATE INDEX IDX_78E70F13BE4A29AF ON promo_code_pricing_groups (pricing_group_id)');
            $this->sql('
            ALTER TABLE
                promo_code_pricing_groups
            ADD
                CONSTRAINT FK_78E70F132FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_pricing_groups
            ADD
                CONSTRAINT FK_78E70F13BE4A29AF FOREIGN KEY (pricing_group_id) REFERENCES pricing_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('ALTER TABLE promo_codes ADD registered_customer_user_only BOOLEAN NOT NULL DEFAULT false');
            $this->sql('ALTER TABLE promo_codes ALTER registered_customer_user_only DROP DEFAULT;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20211018213110')) {
            $this->sql('ALTER TABLE promo_codes DROP on_sale;');
            $this->sql('ALTER TABLE promo_codes DROP in_action;');
            $this->sql('ALTER TABLE promo_codes DROP price_hit;');

            $this->sql('
            CREATE TABLE promo_code_flags (
                promo_code_id INT NOT NULL,
                flag_id INT NOT NULL,
                type VARCHAR(255) NOT NULL,
                PRIMARY KEY(promo_code_id, flag_id)
            )');
            $this->sql('CREATE INDEX IDX_BBCBF8952FAE4625 ON promo_code_flags (promo_code_id)');
            $this->sql('CREATE INDEX IDX_BBCBF895919FE4E5 ON promo_code_flags (flag_id)');
            $this->sql('
            ALTER TABLE
                promo_code_flags
            ADD
                CONSTRAINT FK_BBCBF8952FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                promo_code_flags
            ADD
                CONSTRAINT FK_BBCBF895919FE4E5 FOREIGN KEY (flag_id) REFERENCES flags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220330135536')) {
            $this->sql('
            CREATE TABLE cart_promo_codes (
                cart_id INT NOT NULL,
                promo_code_id INT NOT NULL,
                PRIMARY KEY(cart_id, promo_code_id)
            )');
            $this->sql('CREATE INDEX IDX_5F57049B1AD5CDBF ON cart_promo_codes (cart_id)');
            $this->sql('CREATE INDEX IDX_5F57049B2FAE4625 ON cart_promo_codes (promo_code_id)');
            $this->sql('
            ALTER TABLE
                cart_promo_codes
            ADD
                CONSTRAINT FK_5DE6E36C1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                cart_promo_codes
            ADD
                CONSTRAINT FK_5DE6E36C2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20221208140644')) {
            $this->sql('DROP INDEX countries_code_uni');
            $this->sql('DROP INDEX idx_e52ffdeea76ed395');
            $this->sql('DROP INDEX idx_4e004aaca76ed395');
            $this->sql('ALTER TABLE promo_codes DROP apply_on_second_product');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220413105730')) {
            $this->sql('ALTER TABLE carts ADD transport_id INT DEFAULT NULL');
            $this->sql('ALTER TABLE carts ADD transport_watched_price NUMERIC(20, 6) DEFAULT NULL');
            $this->sql('ALTER TABLE carts ADD pickup_place_identifier VARCHAR(255) DEFAULT NULL');
            $this->sql('COMMENT ON COLUMN carts.transport_watched_price IS \'(DC2Type:money)\'');
            $this->sql('
            ALTER TABLE
                carts
            ADD
                CONSTRAINT FK_4E004AAC9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_4E004AAC9909C13F ON carts (transport_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220421121900')) {
            $this->sql('ALTER TABLE carts ADD payment_id INT DEFAULT NULL');
            $this->sql('ALTER TABLE carts ADD payment_watched_price NUMERIC(20, 6) DEFAULT NULL');
            $this->sql('COMMENT ON COLUMN carts.payment_watched_price IS \'(DC2Type:money)\'');
            $this->sql('ALTER TABLE carts ADD payment_go_pay_bank_swift VARCHAR(15) DEFAULT NULL');
            $this->sql('
            ALTER TABLE
                carts
            ADD
                CONSTRAINT FK_4E004AAC4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_4E004AAC4C3A3BB ON carts (payment_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210427125807')) {
            $this->sql('ALTER TABLE orders ADD tracking_number VARCHAR(100) DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20211101095811')) {
            $this->sql('ALTER TABLE orders ADD pickup_place_identifier VARCHAR(100) DEFAULT NULL;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220201083417')) {
            $this->sql('ALTER TABLE orders ADD go_pay_bank_swift VARCHAR(30) DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200219135254')) {
            $this->sql('ALTER TABLE administrators ADD transfer_issues_last_seen_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
            $this->sql('ALTER TABLE administrators ALTER transfer_issues_last_seen_date_time DROP DEFAULT;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210429125507')) {
            $this->sql('
            CREATE TABLE transport_type_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_11E2A9472C2AC5D3 ON transport_type_translations (translatable_id)');
            $this->sql('
            CREATE UNIQUE INDEX transport_type_translations_uniq_trans ON transport_type_translations (translatable_id, locale)');

            $this->sql('
            CREATE TABLE transport_types (
                id SERIAL NOT NULL,
                code VARCHAR(100) NOT NULL,
                PRIMARY KEY(id)
             )');
            $this->sql('
            ALTER TABLE
                transport_type_translations
            ADD
                CONSTRAINT FK_11E2A9472C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES transport_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE UNIQUE INDEX UNIQ_C43F2EC877153098 ON transport_types (code)');

            $this->sql('INSERT INTO transport_types (code) VALUES (\'' . self::TRANSPORT_TYPE_COMMON . '\')');

            foreach ($this->getAllLocales() as $locale) {
                $this->sql('INSERT INTO transport_type_translations (translatable_id, name, locale) VALUES (1, \'' . t('Standard', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            }

            $this->sql('ALTER TABLE transports ADD transport_type_id INT NOT NULL DEFAULT 1');
            $this->sql('
            ALTER TABLE
                transports
            ADD
                CONSTRAINT FK_C7BE69E5519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_C7BE69E5519B4C62 ON transports (transport_type_id)');
            $this->sql('ALTER TABLE transports ALTER transport_type_id DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210427202020')) {
            $this->sql('ALTER TABLE transport_translations ADD tracking_instruction TEXT DEFAULT NULL;');
            $this->sql('ALTER TABLE transports ADD tracking_url VARCHAR(255) DEFAULT NULL;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210729062922')) {
            $this->sql('ALTER TABLE transports ADD max_weight INT DEFAULT NULL');
            $this->sql('ALTER TABLE products ADD weight INT DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200326125911')) {
            $this->sql('ALTER TABLE transports ADD personal_pickup BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE transports ALTER personal_pickup DROP DEFAULT;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20231004072533')) {
            $this->sql('INSERT INTO transport_types (code) VALUES (\'' . self::TRANSPORT_TYPE_PERSONAL_PICKUP . '\')');
            $lastTransportTypeId = $this->connection->lastInsertId('transport_types_id_seq');

            foreach ($this->getAllLocales() as $locale) {
                $this->sql(
                    'INSERT INTO transport_type_translations (translatable_id, name, locale) 
                        VALUES (
                                ' . $lastTransportTypeId . ', 
                                \'' . t('Personal pickup', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', 
                                \'' . $locale . '\'
                        )',
                );
            }

            $this->sql('UPDATE transports SET transport_type_id = ' . $lastTransportTypeId . ' WHERE personal_pickup = TRUE');

            $this->sql('ALTER TABLE transport_types ALTER code TYPE VARCHAR(25)');
            $this->sql('ALTER TABLE transports DROP personal_pickup');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200219103814')) {
            $this->sql('
            CREATE TABLE transfer_issues (
                id SERIAL NOT NULL,
                transfer_id INT NOT NULL,
                severity VARCHAR(10) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_BF6E22B0537048AF ON transfer_issues (transfer_id)');
            $this->sql('CREATE INDEX IDX_BF6E22B08B8E8428 ON transfer_issues (created_at)');
            $this->sql('
            CREATE TABLE tranfers (
                id SERIAL NOT NULL,
                identifier VARCHAR(100) NOT NULL,
                name VARCHAR(100) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE UNIQUE INDEX UNIQ_57310E34772E836A ON tranfers (identifier)');
            $this->sql('
            ALTER TABLE
                transfer_issues
            ADD
                CONSTRAINT FK_BF6E22B0537048AF FOREIGN KEY (transfer_id) REFERENCES tranfers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210813063216')) {
            $this->sql('INSERT INTO transport_types (code) VALUES (\'' . self::TRANSPORT_TYPE_PACKETERY . '\')');
            $lastTransportTypeId = $this->connection->lastInsertId('transport_types_id_seq');
            $this->sql('INSERT INTO transport_type_translations (translatable_id, name, locale) VALUES (' . $lastTransportTypeId . ', \'Zásilkovna\', \'cs\')');
            $this->sql('INSERT INTO transport_type_translations (translatable_id, name, locale) VALUES (' . $lastTransportTypeId . ', \'Packetery\', \'en\')');

            $this->sql('INSERT INTO tranfers (identifier, name) VALUES (\'PacketeryPacketsExport\', \'Packetery Send packet data to packetery\');');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20240301101223')) {
            $this->sql('
            CREATE INDEX IDX_BF6E22B08B8E84284AF38FD1537048AF ON transfer_issues (
                created_at, deleted_at, transfer_id
            )');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200609074305')) {
            $this->sql('ALTER TABLE promo_codes ADD mass_generate BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE promo_codes ALTER mass_generate DROP DEFAULT');
            $this->sql('ALTER TABLE promo_codes ADD prefix VARCHAR(255) DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200821124308')) {
            $this->sql('ALTER TABLE promo_codes ADD mass_generate_batch_id INT DEFAULT NULL');
        }

        if (!$this->isAppMigrationNotInstalledRemoveIfExists('Version20210902130018')) {
            return;
        }

        $this->sql('ALTER TABLE cart_items ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE cart_items SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE cart_items ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_BEF48445D17F50A6 ON cart_items (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
