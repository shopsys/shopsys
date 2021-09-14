<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210906123951 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
