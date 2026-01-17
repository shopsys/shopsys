<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250929081257 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE autocomplete_favorite_products (
                domain_id INT NOT NULL,
                product_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(product_id, domain_id)
            )');
        $this->sql('CREATE INDEX IDX_4E24C3F4584665A ON autocomplete_favorite_products (product_id)');
        $this->sql('
            CREATE TABLE autocomplete_favorite_categories (
                domain_id INT NOT NULL,
                category_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(category_id, domain_id)
            )');
        $this->sql('CREATE INDEX IDX_D6ADDB8312469DE2 ON autocomplete_favorite_categories (category_id)');
        $this->sql('
            CREATE TABLE autocomplete_favorite_brands (
                domain_id INT NOT NULL,
                brand_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(brand_id, domain_id)
            )');
        $this->sql('CREATE INDEX IDX_E14237F944F5D008 ON autocomplete_favorite_brands (brand_id)');
        $this->sql('
            ALTER TABLE
                autocomplete_favorite_products
            ADD
                CONSTRAINT FK_4E24C3F4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                autocomplete_favorite_categories
            ADD
                CONSTRAINT FK_D6ADDB8312469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                autocomplete_favorite_brands
            ADD
                CONSTRAINT FK_E14237F944F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
