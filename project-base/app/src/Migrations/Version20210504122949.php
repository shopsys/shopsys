<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210504122949 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
