<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

final class Version20200221155940 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $translationId = 1;

        $this->sql('TRUNCATE TABLE flags CASCADE');
        $this->sql('ALTER TABLE flags ADD akeneo_code VARCHAR(255) NOT NULL');

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (1, \'#ffffff\', true, \'flag__product_sale\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 1, \'' . t('Sale', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (2, \'#ffffff\', true, \'flag__product_action\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 2, \'' . t('Action', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (3, \'#ffffff\', true, \'flag__product_new\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 3, \'' . t('New', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (4, \'#ffffff\', true, \'flag__product_made_in_cz\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 4, \'' . t('Made in CZ', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (5, \'#ffffff\', true, \'flag__product_made_in_sk\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 5, \'' . t('Made in SK', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }

        $this->sql('INSERT INTO flags (id, rgb_color, visible, akeneo_code) VALUES (6, \'#ffffff\', true, \'flag__product_made_in_de\')');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (id, translatable_id, name, locale) VALUES (\'' . $translationId . '\', 6, \'' . t('Made in DE', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')');
            $translationId++;
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
