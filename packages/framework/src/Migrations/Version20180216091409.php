<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180216091409 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    public function up(Schema $schema)
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) 
                VALUES (\'personalDataSiteContent\', :domainId, \'\', \'string\')',
                ['domainId' => $domainId]
            );
        }
    }

    public function down(Schema $schema)
    {
    }
}
