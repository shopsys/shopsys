<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20230919173422 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $seoRobotsTxtContent = $this->sql('SELECT value FROM setting_values where name = \'seoRobotsTxtContent\' AND domain_id = :domainId', ['domainId' => $domainId])->fetchOne();

            $seoRobotsTxtLines = explode("\n", $seoRobotsTxtContent);

            if (count(preg_grep('/User-agent: \*/', $seoRobotsTxtLines)) !== 0) {
                continue;
            }

            $seoRobotsTxtContent .= "\n\nUser-agent: *\nCrawl-delay: 3\nRequest-rate: 300/1m";
            $this->sql('UPDATE setting_values SET value = :value where name = \'seoRobotsTxtContent\'  AND domain_id = :domainId', [
                'value' => $seoRobotsTxtContent,
                'domainId' => $domainId,
            ]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
