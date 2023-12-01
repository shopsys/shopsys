<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231124121921 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200207083405')) {
            $this->sql('
            CREATE TABLE product_stocks (
                stock_id INT NOT NULL,
                product_id INT NOT NULL,
                product_quantity INT NOT NULL,
                PRIMARY KEY(stock_id, product_id)
            )');
            $this->sql('CREATE INDEX IDX_348BD9A1DCD6110 ON product_stocks (stock_id)');
            $this->sql('CREATE INDEX IDX_348BD9A14584665A ON product_stocks (product_id)');
            $this->sql('
            CREATE TABLE stocks (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                is_default BOOLEAN NOT NULL,
                external_id VARCHAR(255) DEFAULT NULL,
                note TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE UNIQUE INDEX UNIQ_56F798059F75D7B0 ON stocks (external_id)');
            $this->sql('
            ALTER TABLE
                product_stocks
            ADD
                CONSTRAINT FK_348BD9A1DCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                product_stocks
            ADD
                CONSTRAINT FK_348BD9A14584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            $this->sql('
            CREATE TABLE stock_domains (
                id SERIAL NOT NULL,
                stock_id INT NOT NULL,
                domain_id INT NOT NULL,
                is_enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_95DDAF3EDCD6110 ON stock_domains (stock_id)');
            $this->sql('CREATE UNIQUE INDEX stock_domain ON stock_domains (stock_id, domain_id)');
            $this->sql('
            ALTER TABLE
                stock_domains
            ADD
                CONSTRAINT FK_95DDAF3EDCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200317140613')) {
            $this->sql('ALTER TABLE product_stocks ADD product_exposed BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE product_stocks ALTER product_exposed DROP DEFAULT;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200907111826')) {
            $this->sql('ALTER TABLE stocks ADD position INT NOT NULL DEFAULT 1');
            $this->sql('ALTER TABLE stocks ALTER position DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210421103608')) {
            $this->sql('
            CREATE TABLE stores (
                id SERIAL NOT NULL,
                stock_id INT DEFAULT NULL,
                is_default BOOLEAN NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                external_id VARCHAR(255) NULL,
                address TEXT DEFAULT NULL,
                opening_hours TEXT DEFAULT NULL,
                contact_info TEXT DEFAULT NULL,
                special_message TEXT DEFAULT NULL,
                location_latitude NUMERIC(16, 13) DEFAULT NULL,
                location_longitude NUMERIC(16, 13) DEFAULT NULL,
                position INT NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE UNIQUE INDEX UNIQ_D5907CCC9F75D7B0 ON stores (external_id)');
            $this->sql('
            CREATE TABLE store_domains (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                store_id INT NOT NULL,
                is_enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_95739E7AB092A811 ON store_domains (store_id)');
            $this->sql('
            ALTER TABLE
                store_domains
            ADD
                CONSTRAINT FK_95739E7AB092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE UNIQUE INDEX store_domain ON store_domains (store_id, domain_id)');
            $this->sql('
            ALTER TABLE
                stores
            ADD
                CONSTRAINT FK_D5907CCCDCD6110 FOREIGN KEY (stock_id) REFERENCES stocks (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_D5907CCCDCD6110 ON stores (stock_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210629054204')) {
            $this->sql('ALTER TABLE stores ADD uuid UUID DEFAULT NULL');
            $this->sql('UPDATE stores SET uuid = uuid_generate_v4()');
            $this->sql('ALTER TABLE stores ALTER uuid SET NOT NULL');
            $this->sql('CREATE UNIQUE INDEX UNIQ_D5907CCCD17F50A6 ON stores (uuid)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210711163712')) {
            $this->sql('CREATE TABLE product_stores (product_id INT NOT NULL, store_id INT NOT NULL, product_exposed BOOLEAN NOT NULL, PRIMARY KEY(product_id, store_id))');
            $this->sql('CREATE INDEX IDX_B7EC3D684584665A ON product_stores (product_id)');
            $this->sql('CREATE INDEX IDX_B7EC3D68B092A811 ON product_stores (store_id)');
            $this->sql('ALTER TABLE product_stores ADD CONSTRAINT FK_B7EC3D684584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('ALTER TABLE product_stores ADD CONSTRAINT FK_B7EC3D68B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            $this->sql('ALTER TABLE product_stocks DROP product_exposed');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210723064416')) {
            $this->sql('ALTER TABLE stores DROP address');
            $this->sql('ALTER TABLE stores ADD country_id INT NOT NULL DEFAULT 1');
            $this->sql('ALTER TABLE stores ALTER country_id DROP DEFAULT;');
            $this->sql('ALTER TABLE stores ADD street VARCHAR(100) NOT NULL DEFAULT \'\'');
            $this->sql('ALTER TABLE stores ALTER street DROP DEFAULT;');
            $this->sql('ALTER TABLE stores ADD city VARCHAR(100) NOT NULL DEFAULT \'\'');
            $this->sql('ALTER TABLE stores ALTER city DROP DEFAULT;');
            $this->sql('ALTER TABLE stores ADD postcode VARCHAR(30) NOT NULL DEFAULT \'\'');
            $this->sql('ALTER TABLE stores ALTER postcode DROP DEFAULT;');
            $this->sql('
            ALTER TABLE
                stores
            ADD
                CONSTRAINT FK_D5907CCCF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_D5907CCCF92F3E70 ON stores (country_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230404120748')) {
            $this->sql('ALTER TABLE stores ALTER location_latitude TYPE VARCHAR(255)');
            $this->sql('ALTER TABLE stores ALTER location_longitude TYPE VARCHAR(255)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230518061121')) {
            $this->sql('
            CREATE TABLE store_opening_hours (
                id SERIAL NOT NULL,
                store_id INT NOT NULL,
                day_of_week INT NOT NULL,
                first_opening_time VARCHAR(5) DEFAULT NULL,
                first_closing_time VARCHAR(5) DEFAULT NULL,
                second_opening_time VARCHAR(5) DEFAULT NULL,
                second_closing_time VARCHAR(5) DEFAULT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_7E35C95B092A811 ON store_opening_hours (store_id)');

            $this->sql('
            ALTER TABLE
                store_opening_hours
            ADD
                CONSTRAINT FK_7E35C95B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230524064748')) {
            $this->sql('
            CREATE TABLE closed_days (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                date DATE NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('COMMENT ON COLUMN closed_days.date IS \'(DC2Type:date_immutable)\'');
            $this->sql('
            CREATE TABLE closed_day_excluded_stores (
                closed_day_id INT NOT NULL,
                store_id INT NOT NULL,
                PRIMARY KEY(closed_day_id, store_id)
            )');
            $this->sql('CREATE INDEX IDX_B4EC517608F9E8F ON closed_day_excluded_stores (closed_day_id)');
            $this->sql('CREATE INDEX IDX_B4EC517B092A811 ON closed_day_excluded_stores (store_id)');

            $this->sql('
            ALTER TABLE
                closed_day_excluded_stores
            ADD
                CONSTRAINT FK_B4EC517608F9E8F FOREIGN KEY (closed_day_id) REFERENCES closed_days (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                closed_day_excluded_stores
            ADD
                CONSTRAINT FK_B4EC517B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230816092114')) {
            $this->sql('ALTER TABLE closed_days ALTER date TYPE DATE');
            $this->sql('COMMENT ON COLUMN closed_days.date IS \'(DC2Type:date_immutable)\'');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200331114558')) {
            $this->sql('ALTER TABLE order_items ADD personal_pickup_stock_id INT DEFAULT NULL');
            $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB078AE585C FOREIGN KEY (personal_pickup_stock_id) REFERENCES stocks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_62809DB078AE585C ON order_items (personal_pickup_stock_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210712170052')) {
            $this->sql('ALTER TABLE order_items DROP CONSTRAINT FK_62809DB078AE585C');
            $this->sql('DROP INDEX IDX_62809DB078AE585C');
            $this->sql('ALTER TABLE order_items RENAME COLUMN personal_pickup_stock_id TO personal_pickup_store_id');
            $this->sql('
                ALTER TABLE
                    order_items
                ADD
                    CONSTRAINT FK_62809DB0C5F1915D FOREIGN KEY (personal_pickup_store_id) REFERENCES stores (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_62809DB0C5F1915D ON order_items (personal_pickup_store_id)');
        }

        if ($this->columnExists('stores', 'opening_hours')) {
            $this->sql('ALTER TABLE stores DROP opening_hours');
        }

        if ($this->tableExists('product_stores')) {
            $this->sql('DROP TABLE product_stores');
        }

        $this->sql('ALTER TABLE order_items DROP CONSTRAINT FK_62809DB0C5F1915D');
        $this->sql('DROP INDEX IDX_62809DB0C5F1915D');
        $this->sql('ALTER TABLE order_items DROP COLUMN personal_pickup_store_id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
