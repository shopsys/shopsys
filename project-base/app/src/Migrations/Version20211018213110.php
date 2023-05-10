<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20211018213110 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
