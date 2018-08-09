<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161125140534 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('
            CREATE TABLE categories_top (
                category_id INT NOT NULL,
                domain_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(category_id, domain_id)
            )');
        $this->sql('CREATE INDEX IDX_20AA436212469DE2 ON categories_top (category_id)');
        $this->sql('
            ALTER TABLE
                categories_top
            ADD
                CONSTRAINT FK_20AA436212469DE2 FOREIGN KEY (category_id)
                    REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
    }
}
