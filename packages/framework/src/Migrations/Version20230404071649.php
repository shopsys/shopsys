<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230404071649 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $seoRobotsTxtContentSettingCount = $this->sql(
                'SELECT COUNT(*) FROM setting_values WHERE name = \'seoRobotsTxtContent\' AND domain_id = :domainId;',
                ['domainId' => $domainId],
            )->fetchOne();

            if ($seoRobotsTxtContentSettingCount <= 0) {
                $this->sql(
                    'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'seoRobotsTxtContent\', :domainId, \'\', \'string\')',
                    ['domainId' => $domainId],
                );
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
