<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200221155940 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('TRUNCATE TABLE flags CASCADE');
        $this->sql('ALTER TABLE flags ADD akeneo_code VARCHAR(255) NOT NULL');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (1, \'#ffffff\', true, \'flag__product_sale\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (1, 1, \'Výprodej\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (2, 1, \'Výpredaj\', \'sk\')');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (2, \'#ffffff\', true, \'flag__product_action\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (3, 2, \'Akce\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (4, 2, \'Akcia\', \'sk\')');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (3, \'#ffffff\', true, \'flag__product_new\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (5, 3, \'Novinka\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (6, 3, \'Novinka\', \'sk\')');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (4, \'#ffffff\', true, \'flag__product_made_in_cz\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (7, 4, \'Vyrobeno v ČR\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (8, 4, \'Vyrobeno v ČR\', \'sk\')');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (5, \'#ffffff\', true, \'flag__product_made_in_sk\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (9, 5, \'Vyrobeno v SK\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (10, 5, \'Vyrobeno v SK\', \'sk\')');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (6, \'#ffffff\', true, \'flag__product_made_in_de\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (11, 6, \'Vyrobeno v DE\', \'cs\')');
        $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (12, 6, \'Vyrobeno v DE\', \'sk\')');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
