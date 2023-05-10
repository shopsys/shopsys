<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230217093923 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE compared_items (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                comparison_id INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_9D3D050D4584665A ON compared_items (product_id)');
        $this->sql('CREATE INDEX IDX_9D3D050DE4EC5411 ON compared_items (comparison_id)');
        $this->sql('
            CREATE TABLE comparisons (
                id SERIAL NOT NULL,
                customer_user_id INT DEFAULT NULL,
                uuid UUID NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_745BF22CD17F50A6 ON comparisons (uuid)');
        $this->sql('CREATE INDEX IDX_745BF22CBBB3772B ON comparisons (customer_user_id)');
        $this->sql('
            ALTER TABLE
                compared_items
            ADD
                CONSTRAINT FK_9D3D050D4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                compared_items
            ADD
                CONSTRAINT FK_9D3D050DE4EC5411 FOREIGN KEY (comparison_id) REFERENCES comparisons (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                comparisons
            ADD
                CONSTRAINT FK_745BF22CBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
