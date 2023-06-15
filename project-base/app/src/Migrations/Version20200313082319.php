<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200313082319 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE category_parameters (
                category_id INT NOT NULL,
                parameter_id INT NOT NULL,
                PRIMARY KEY(category_id, parameter_id)
            )');
        $this->sql('CREATE INDEX IDX_208D188012469DE2 ON category_parameters (category_id)');
        $this->sql('CREATE INDEX IDX_208D18807C56DBD6 ON category_parameters (parameter_id)');
        $this->sql('
            ALTER TABLE
                category_parameters
            ADD
                CONSTRAINT FK_208D188012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                category_parameters
            ADD
                CONSTRAINT FK_208D18807C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
