<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20200714071640 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $isFlagProductHit = $this->sql('SELECT count(*) FROM flags WHERE akeneo_code =\'flag__product_hit\'')->fetchOne();

        if ($isFlagProductHit !== 0) {
            return;
        }

        $this->sql('INSERT INTO flags (rgb_color, visible, akeneo_code) VALUES (\'#ffffff\', true, \'flag__product_hit\')');
        $lastFlagsId = $this->connection->lastInsertId('flags_id_seq');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(sprintf('INSERT INTO flag_translations (translatable_id, name, locale) VALUES (%d, \'' . t('Price hit', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', \'' . $locale . '\')', $lastFlagsId));
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
