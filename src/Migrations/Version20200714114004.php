<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200714114004 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
