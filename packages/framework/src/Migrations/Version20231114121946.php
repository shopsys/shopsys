<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20231114121946 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE feed_modules (
                name VARCHAR(255) NOT NULL,
                domain_id INT NOT NULL,
                scheduled BOOLEAN NOT NULL,
                PRIMARY KEY(name, domain_id)
            )');
    }
}
