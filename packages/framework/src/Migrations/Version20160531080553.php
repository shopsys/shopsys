<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160531080553 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
            (\'feedDomainIdToContinue\', 0, NULL, \'string\'),
            (\'feedItemIdToContinue\', 0, NULL, \'string\'),
            (\'feedNameToContinue\', 0, NULL, \'string\')
        ');
    }
}
