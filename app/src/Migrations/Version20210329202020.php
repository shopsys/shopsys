<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210329202020 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE administrators ADD two_factor_authentication_type VARCHAR(32)');
        $this->sql('ALTER TABLE administrators ADD email_authentication_code VARCHAR(16)');
        $this->sql('ALTER TABLE administrators ADD google_authenticator_secret VARCHAR(255)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
