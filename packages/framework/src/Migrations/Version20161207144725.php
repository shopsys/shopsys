<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161207144725 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    public function up(Schema $schema)
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('DELETE FROM migrations WHERE version = \'201601207144725\';');

            $phoneHours = $this->sql('SELECT COUNT(*) FROM setting_values WHERE name = \'shopInfoPhoneHours\' AND domain_id = :domainId;
            ', ['domainId' => $domainId])->fetchColumn(0);

            if ($phoneHours <= 0) {
                $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
                    (\'shopInfoPhoneHours\', :domainId, \'(po-pá, 10:00 - 16:00)\', \'string\');
                ', ['domainId' => $domainId]);
            }
        }
    }

    public function down(Schema $schema)
    {
    }
}
