<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230404071649 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $seoRobotsContentSettingCount = $this->sql(
            'SELECT COUNT(*) FROM setting_values WHERE name = \'seoRobotsContent\' AND domain_id = 1;'
        )->fetchOne();

        if ($seoRobotsContentSettingCount <= 0) {
            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'seoRobotsContent\', 1, NULL, \'string\')',
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
