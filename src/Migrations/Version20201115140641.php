<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20201115140641 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE linked_categories (
                id SERIAL NOT NULL,
                parent_category_id INT NOT NULL,
                category_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_AD379D2F796A8F92 ON linked_categories (parent_category_id)');
        $this->sql('CREATE INDEX IDX_AD379D2F12469DE2 ON linked_categories (category_id)');
        $this->sql('ALTER TABLE linked_categories
            ADD CONSTRAINT FK_AD379D2F796A8F92 FOREIGN KEY (parent_category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE linked_categories
            ADD CONSTRAINT FK_AD379D2F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
