<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200325124454 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE parameter_units (id SERIAL NOT NULL, unit VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->sql('CREATE UNIQUE INDEX UNIQ_99758EC5DCBB0C53 ON parameter_units (unit)');
        $this->sql('
            CREATE TABLE parameter_unit_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_74B523A92C2AC5D3 ON parameter_unit_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX parameter_unit_translations_uniq_trans ON parameter_unit_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                parameter_unit_translations
            ADD
                CONSTRAINT FK_74B523A92C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES parameter_units (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE parameters ADD parameter_unit_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                parameters
            ADD
                CONSTRAINT FK_69348FE32E3CC8D FOREIGN KEY (parameter_unit_id) REFERENCES parameter_units (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_69348FE32E3CC8D ON parameters (parameter_unit_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
