<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161207135225 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('DELETE FROM migrations WHERE version = \'201601207135225\';');

            $phoneNumber = $this->sqlQuery(
                'SELECT COUNT(*) FROM setting_values WHERE name = \'shopInfoPhoneNumber\' AND domain_id = :domainId;
            ',
                ['domainId' => $domainId],
            )->fetchOne();
            $infoMail = $this->sqlQuery(
                'SELECT COUNT(*) FROM setting_values WHERE name = \'shopInfoEmail\' AND domain_id = :domainId;
            ',
                ['domainId' => $domainId],
            )->fetchOne();

            if ($phoneNumber <= 0) {
                $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
                    (\'shopInfoPhoneNumber\', :domainId, \'+420123456789\', \'string\');
                ', ['domainId' => $domainId]);
            }

            if ($infoMail <= 0) {
                $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
                (\'shopInfoEmail\', :domainId, \'no-reply@shopsys.com\', \'string\');
            ', ['domainId' => $domainId]);
            }
        }
    }
}
