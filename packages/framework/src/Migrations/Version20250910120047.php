<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250910120047 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE gift_plans (
                id SERIAL NOT NULL,
                gift_product_id INT NOT NULL,
                uuid UUID NOT NULL,
                domain_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_204FC86AD17F50A6 ON gift_plans (uuid)');
        $this->sql('CREATE INDEX IDX_204FC86AE875A1B1 ON gift_plans (gift_product_id)');
        $this->sql('
            CREATE TABLE gift_plan_main_products (
                gift_plan_id INT NOT NULL,
                main_product_id INT NOT NULL,
                PRIMARY KEY(gift_plan_id, main_product_id)
            )');
        $this->sql('CREATE INDEX IDX_E5E05EB584C33134 ON gift_plan_main_products (gift_plan_id)');
        $this->sql('CREATE INDEX IDX_E5E05EB57D7C1239 ON gift_plan_main_products (main_product_id)');
        $this->sql('
            ALTER TABLE
                gift_plans
            ADD
                CONSTRAINT FK_204FC86AE875A1B1 FOREIGN KEY (gift_product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                gift_plan_main_products
            ADD
                CONSTRAINT FK_E5E05EB584C33134 FOREIGN KEY (gift_plan_id) REFERENCES gift_plans (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                gift_plan_main_products
            ADD
                CONSTRAINT FK_E5E05EB57D7C1239 FOREIGN KEY (main_product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
