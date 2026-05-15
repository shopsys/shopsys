<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180531141640 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE category_domains DROP CONSTRAINT "category_domains_pkey"');
        $this->sql('ALTER TABLE category_domains ADD id SERIAL NOT NULL');
        $this->sql('ALTER TABLE category_domains ADD PRIMARY KEY (id)');
        $this->sql('CREATE UNIQUE INDEX category_domain ON category_domains (category_id, domain_id)');

        $this->sql('ALTER TABLE category_domains RENAME COLUMN hidden TO enabled');
        $this->sql('UPDATE category_domains SET enabled = NOT enabled');
    }
}
