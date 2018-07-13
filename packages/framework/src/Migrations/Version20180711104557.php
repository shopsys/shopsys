<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180711104557 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('UPDATE orders SET country_id = 1 WHERE country_id = 3');
        $this->sql('UPDATE orders SET country_id = 2 WHERE country_id = 4');
        $this->sql('UPDATE orders SET delivery_country_id = 1 WHERE delivery_country_id = 3');
        $this->sql('UPDATE orders SET delivery_country_id = 2 WHERE delivery_country_id = 4');

        $this->sql('UPDATE billing_addresses SET country_id = 1 WHERE country_id = 3');
        $this->sql('UPDATE billing_addresses SET country_id = 2 WHERE country_id = 4');

        $this->sql('UPDATE delivery_addresses SET country_id = 1 WHERE country_id = 3');
        $this->sql('UPDATE delivery_addresses SET country_id = 2 WHERE country_id = 4');

        $this->sql('DELETE FROM countries WHERE id NOT IN (1, 2)');

        $this->sql('ALTER TABLE countries DROP COLUMN name');

        $this->sql('
            CREATE TABLE country_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_CA1456952C2AC5D3 ON country_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX country_translations_uniq_trans ON country_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                country_translations
            ADD
                CONSTRAINT FK_CA1456952C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES countries (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql(
            'INSERT INTO country_translations (translatable_id, name, locale) VALUES
                    (1, \'Czech republic\', \'en\'),
                    (2, \'Slovakia\', \'en\'),
                    (1, \'Česká republika\', \'cs\'),
                    (2, \'Slovenská republika\', \'cs\')
                  ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
