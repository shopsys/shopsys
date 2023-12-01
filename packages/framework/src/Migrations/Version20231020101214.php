<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231020101214 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_list_items (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                product_list_id INT NOT NULL,
                uuid UUID NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_ABEA2265D17F50A6 ON product_list_items (uuid)');
        $this->sql('CREATE INDEX IDX_ABEA22654584665A ON product_list_items (product_id)');
        $this->sql('CREATE INDEX IDX_ABEA2265EC770D3B ON product_list_items (product_list_id)');
        $this->sql('COMMENT ON COLUMN product_list_items.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('
            CREATE TABLE product_lists (
                id SERIAL NOT NULL,
                customer_user_id INT DEFAULT NULL,
                uuid UUID NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                type VARCHAR(20) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_A97AE4F9D17F50A6 ON product_lists (uuid)');
        $this->sql('CREATE INDEX IDX_A97AE4F9BBB3772B ON product_lists (customer_user_id)');
        $this->sql('COMMENT ON COLUMN product_lists.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN product_lists.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('
            ALTER TABLE
                product_list_items
            ADD
                CONSTRAINT FK_ABEA22654584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_list_items
            ADD
                CONSTRAINT FK_ABEA2265EC770D3B FOREIGN KEY (product_list_id) REFERENCES product_lists (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_lists
            ADD
                CONSTRAINT FK_A97AE4F9BBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
