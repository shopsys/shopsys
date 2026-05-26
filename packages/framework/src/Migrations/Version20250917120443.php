<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250917120443 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payment_domains ADD account_number VARCHAR(50) DEFAULT NULL');
        $this->sql('ALTER TABLE payment_domains ALTER account_number DROP DEFAULT');
        $this->sql('ALTER TABLE payment_domains ADD iban VARCHAR(50) DEFAULT NULL');
        $this->sql('ALTER TABLE payment_domains ALTER iban DROP DEFAULT');
        $this->sql('ALTER TABLE payment_domains ADD bic_swift VARCHAR(50) DEFAULT NULL');
        $this->sql('ALTER TABLE payment_domains ALTER bic_swift DROP DEFAULT');
    }
}
