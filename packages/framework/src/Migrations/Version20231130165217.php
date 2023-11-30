<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231130165217 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if (!$this->columnExists('product_domains', 'short_description_usp1')) {
            $this->sql('ALTER TABLE product_domains ADD short_description_usp1 VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_domains ALTER short_description_usp1 DROP DEFAULT');
        }

        if (!$this->columnExists('product_domains', 'short_description_usp2')) {
            $this->sql('ALTER TABLE product_domains ADD short_description_usp2 VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_domains ALTER short_description_usp2 DROP DEFAULT');
        }

        if (!$this->columnExists('product_domains', 'short_description_usp3')) {
            $this->sql('ALTER TABLE product_domains ADD short_description_usp3 VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_domains ALTER short_description_usp3 DROP DEFAULT');
        }

        if (!$this->columnExists('product_domains', 'short_description_usp4')) {
            $this->sql('ALTER TABLE product_domains ADD short_description_usp4 VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_domains ALTER short_description_usp4 DROP DEFAULT');
        }

        if (!$this->columnExists('product_domains', 'short_description_usp5')) {
            $this->sql('ALTER TABLE product_domains ADD short_description_usp5 VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_domains ALTER short_description_usp5 DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200323123356')) {
            $this->sql('
                CREATE TABLE product_domain_flags (
                    product_domain_id INT NOT NULL,
                    flag_id INT NOT NULL,
                    PRIMARY KEY(product_domain_id, flag_id)
            )');
            $this->sql('CREATE INDEX IDX_55DA4B077FFFB868 ON product_domain_flags (product_domain_id)');
            $this->sql('CREATE INDEX IDX_55DA4B07919FE4E5 ON product_domain_flags (flag_id)');
            $this->sql('
                ALTER TABLE
                    product_domain_flags
                ADD
                    CONSTRAINT FK_55DA4B077FFFB868 FOREIGN KEY (product_domain_id) REFERENCES product_domains (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
                ALTER TABLE
                    product_domain_flags
                ADD
                    CONSTRAINT FK_55DA4B07919FE4E5 FOREIGN KEY (flag_id) REFERENCES flags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            $this->sql('
                INSERT INTO product_domain_flags (product_domain_id, flag_id)
                SELECT product_domains.id AS product_domain_id, product_flags.flag_id AS flag_id
                FROM product_domains, product_flags
                WHERE product_flags.product_id = product_domains.product_id
            ');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200714091100')) {
            $this->sql('ALTER TABLE product_domains ADD domain_ordering_priority INT NOT NULL DEFAULT 0');
            $this->sql('ALTER TABLE product_domains ALTER domain_ordering_priority DROP DEFAULT');

            $this->sql('
                UPDATE product_domains
                SET domain_ordering_priority = products.ordering_priority
                FROM products
                WHERE product_domains.product_id = products.id AND products.ordering_priority > 0
            ');
        }

        $this->sql('ALTER TABLE products DROP ordering_priority');
        $this->sql('ALTER TABLE product_domains RENAME COLUMN domain_ordering_priority TO ordering_priority');
        $this->sql('DROP TABLE product_flags');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
