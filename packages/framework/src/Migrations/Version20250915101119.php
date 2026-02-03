<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250915101119 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE flags ADD locked_for_deletion BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE flags ALTER locked_for_deletion DROP DEFAULT');

        $this->sql('INSERT INTO flags (uuid, visible, rgb_color, locked_for_deletion) VALUES (\'3b709b97-604d-462d-b045-885f126e78d2\', true, \'#000000\', true)');
        $flagId = $this->connection->lastInsertId();

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO flag_translations (name, translatable_id, locale) VALUES (:name, :flagId, :locale)', [
                'name' => t('Product gift', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
                'flagId' => $flagId,
                'locale' => $locale,
            ]);
        }
    }
}
