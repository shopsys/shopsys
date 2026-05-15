<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260320122217 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $unitCount = $this->sqlQuery('SELECT COUNT(*) FROM units')->fetchOne();

        if ($unitCount > 0) {
            return;
        }

        $this->sql('INSERT INTO units (id) VALUES (DEFAULT)');
        $unitId = $this->connection->lastInsertId();

        foreach ($this->getAllLocales() as $locale) {
            $name = t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->sql(
                'INSERT INTO unit_translations (translatable_id, name, locale) VALUES (:unitId, :name, :locale)',
                [
                    'unitId' => $unitId,
                    'name' => $name,
                    'locale' => $locale,
                ],
            );
        }

        $this->sql(
            'UPDATE setting_values SET value = :unitId WHERE name = \'defaultUnitId\' AND domain_id = 0',
            ['unitId' => $unitId],
        );
    }
}
