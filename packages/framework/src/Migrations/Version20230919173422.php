<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20230919173422 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $seoRobotsTxtContent = "Crawl-delay: 0.3\nRequest-rate: 300/1m";
            $this->sql('UPDATE setting_values SET value = :value where name = \'seoRobotsTxtContent\'  AND domain_id = :domainId', [
                'value' => $seoRobotsTxtContent,
                'domainId' => $domainId,
            ]);
        }
    }
}
