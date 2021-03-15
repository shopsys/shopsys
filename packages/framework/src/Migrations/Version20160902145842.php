<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;

class Version20160902145842 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach ($this->getCreatedDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
                (\'heurekaWidgetCode\', :domainId, null, \'string\');
            ', ['domainId' => $domainId]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
