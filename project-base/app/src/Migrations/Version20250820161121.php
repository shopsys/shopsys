<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250820161121 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE language_constants ADD namespace VARCHAR(100) NOT NULL DEFAULT \'common\'');

        $this->sql('ALTER TABLE language_constants ALTER namespace DROP DEFAULT');

        $this->sql('DROP INDEX language_constants_key');

        $this->sql('CREATE UNIQUE INDEX language_constants_key_namespace ON language_constants (key, namespace)');
    }
}
