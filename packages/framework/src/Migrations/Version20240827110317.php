<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240827110317 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200129140317')) {
            foreach ($this->getAllDomainIds() as $domainId) {
                $this->sql(
                    'INSERT INTO "setting_values" ("name", "domain_id", "value", "type") VALUES 
                    (\'transferDaysBetweenStocks\', :domainId, \'1\', \'integer\')',
                    ['domainId' => $domainId],
                );
            }
        }
    }
}
